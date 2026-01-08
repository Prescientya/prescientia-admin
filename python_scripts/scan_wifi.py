import subprocess
import re
import json
import sys

try:
    output = subprocess.check_output(
        ["netsh", "wlan", "show", "networks", "mode=bssid"],
        text=True,
        encoding="utf-8",
        errors="ignore"
    )

    networks = []
    current_ssid = None

    for line in output.splitlines():
        ssid_match = re.match(r"\s*SSID\s+\d+\s*:\s*(.*)", line)
        if ssid_match:
            current_ssid = {
                "ssid": ssid_match.group(1).strip(),
                "bssids": []
            }
            networks.append(current_ssid)
            continue

        bssid_match = re.match(r"\s*BSSID\s+\d+\s*:\s*(.*)", line)
        if bssid_match and current_ssid:
            current_ssid["bssids"].append({
                "bssid": bssid_match.group(1).strip(),
                "signal": None,
                "channel": None,
                "radio": None,
                "security": None,
                "encryption": None
            })
            continue

        if current_ssid and current_ssid["bssids"]:
            signal = re.search(r"Signal\s*:\s*(\d+)%", line)
            channel = re.search(r"Channel\s*:\s*(\d+)", line)
            radio = re.search(r"Radio type\s*:\s*(.*)", line)
            auth = re.search(r"Authentication\s*:\s*(.*)", line)
            enc = re.search(r"Encryption\s*:\s*(.*)", line)

            bssid = current_ssid["bssids"][-1]

            if signal:
                bssid["signal"] = int(signal.group(1))
            if channel:
                bssid["channel"] = int(channel.group(1))
            if radio:
                bssid["radio"] = radio.group(1).strip()
            if auth:
                bssid["security"] = auth.group(1).strip()
            if enc:
                bssid["encryption"] = enc.group(1).strip()

    print(json.dumps(networks, indent=2, ensure_ascii=False))
    sys.exit(0)

except Exception as e:
    error_response = {
        "error": str(e),
        "message": "Gagal memindai WiFi"
    }
    print(json.dumps(error_response, indent=2, ensure_ascii=False))
    sys.exit(1)
