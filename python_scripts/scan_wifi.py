import subprocess
import re
import json
import sys
import platform

def scan_wifi_windows():
    """Scan WiFi networks on Windows using netsh"""
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

        return networks
    except Exception as e:
        raise Exception(f"Windows WiFi scan failed: {str(e)}")


def scan_wifi_linux():
    """Scan WiFi networks on Linux using nmcli"""
    try:
        # Try nmcli first (NetworkManager)
        try:
            output = subprocess.check_output(
                ["nmcli", "dev", "wifi", "list"],
                text=True,
                errors="ignore"
            )
            return parse_nmcli_output(output)
        except FileNotFoundError:
            # Fallback to iw command
            output = subprocess.check_output(
                ["sudo", "iw", "dev", "wlan0", "scan"],
                text=True,
                errors="ignore"
            )
            return parse_iw_output(output)
    except Exception as e:
        raise Exception(f"Linux WiFi scan failed: {str(e)}")


def parse_nmcli_output(output):
    """Parse nmcli output format"""
    networks = {}
    
    for line in output.splitlines()[1:]:  # Skip header
        if not line.strip():
            continue
        
        # nmcli format: SSID BSSID CHANNEL FREQ RATE SIGNAL BARS SECURITY
        parts = line.split()
        if len(parts) >= 8:
            ssid = parts[0]
            bssid = parts[1]
            
            if ssid not in networks:
                networks[ssid] = {
                    "ssid": ssid,
                    "bssids": []
                }
            
            # Extract signal strength (usually around index -3)
            signal_match = re.search(r'(\d+)\s+▂', line)
            signal = int(signal_match.group(1)) if signal_match else None
            
            networks[ssid]["bssids"].append({
                "bssid": bssid,
                "signal": signal,
                "channel": parts[2] if len(parts) > 2 else None,
                "radio": None,
                "security": ' '.join(parts[7:]) if len(parts) > 7 else None,
                "encryption": None
            })
    
    return list(networks.values())


def parse_iw_output(output):
    """Parse iw command output format"""
    networks = {}
    current_ssid = None
    current_bssid = None
    
    for line in output.splitlines():
        # Look for SSID
        ssid_match = re.search(r'SSID:\s*(.*)', line)
        if ssid_match:
            ssid = ssid_match.group(1).strip()
            if ssid and ssid not in networks:
                networks[ssid] = {
                    "ssid": ssid,
                    "bssids": []
                }
                current_ssid = ssid
            continue
        
        # Look for BSSID
        bssid_match = re.search(r'([0-9a-fA-F]{2}(?::[0-9a-fA-F]{2}){5})', line)
        if bssid_match and current_ssid:
            current_bssid = bssid_match.group(1)
            networks[current_ssid]["bssids"].append({
                "bssid": current_bssid,
                "signal": None,
                "channel": None,
                "radio": None,
                "security": None,
                "encryption": None
            })
            continue
        
        # Look for signal strength
        signal_match = re.search(r'signal:\s*(-?\d+)', line)
        if signal_match and current_ssid and networks[current_ssid]["bssids"]:
            networks[current_ssid]["bssids"][-1]["signal"] = int(signal_match.group(1))
        
        # Look for frequency/channel
        freq_match = re.search(r'(\d+)\s*MHz', line)
        if freq_match and current_ssid and networks[current_ssid]["bssids"]:
            freq = int(freq_match.group(1))
            # Rough conversion from frequency to channel
            if 2400 <= freq <= 2500:
                channel = int((freq - 2407) / 5)
            elif 5000 <= freq <= 6000:
                channel = int((freq - 5000) / 5)
            else:
                channel = None
            networks[current_ssid]["bssids"][-1]["channel"] = channel
    
    return list(networks.values()) if networks else []


def scan_wifi_macos():
    """Scan WiFi networks on macOS using airport"""
    try:
        airport_cmd = "/System/Library/PrivateFrameworks/Apple80211.framework/Versions/Current/Resources/airport"
        output = subprocess.check_output(
            [airport_cmd, "-s"],
            text=True,
            errors="ignore"
        )
        
        networks = {}
        
        for line in output.splitlines()[1:]:  # Skip header
            if not line.strip():
                continue
            
            # Format: SSID BSSID RSSI CHANNEL HT CC SECURITY
            parts = line.split()
            if len(parts) >= 7:
                ssid = parts[0]
                bssid = parts[1]
                signal = int(parts[2]) if parts[2].lstrip('-').isdigit() else None
                channel = int(parts[3]) if parts[3].isdigit() else None
                
                if ssid not in networks:
                    networks[ssid] = {
                        "ssid": ssid,
                        "bssids": []
                    }
                
                networks[ssid]["bssids"].append({
                    "bssid": bssid,
                    "signal": signal,
                    "channel": channel,
                    "radio": parts[4] if len(parts) > 4 else None,
                    "security": ' '.join(parts[6:]) if len(parts) > 6 else None,
                    "encryption": None
                })
        
        return list(networks.values())
    except Exception as e:
        raise Exception(f"macOS WiFi scan failed: {str(e)}")


def get_networks():
    """Main function to scan WiFi networks based on OS"""
    os_type = platform.system()
    
    try:
        if os_type == "Windows":
            return scan_wifi_windows()
        elif os_type == "Linux":
            return scan_wifi_linux()
        elif os_type == "Darwin":
            return scan_wifi_macos()
        else:
            raise Exception(f"Unsupported operating system: {os_type}")
    except Exception as e:
        raise Exception(f"WiFi scan failed: {str(e)}")


try:
    networks = get_networks()
    print(json.dumps(networks, indent=2, ensure_ascii=False))
    sys.exit(0)

except Exception as e:
    error_response = {
        "error": str(e),
        "message": "Gagal memindai WiFi",
        "os": platform.system()
    }
    print(json.dumps(error_response, indent=2, ensure_ascii=False))
    sys.exit(1)
