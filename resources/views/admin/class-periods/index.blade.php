@extends('layouts.app')
<style>
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;               /* default tersembunyi */
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    
    .modal-container {
      background: white;
      padding: 24px;
      border-radius: 12px;
      width: 600px;
      max-width: 90%;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
</style>


@section('content')
<button class="btn btn-primary" onclick="openImportModal()">Import</button>

<div class="modal-overlay" id="importModal">
  <div class="modal-container">

    <div class="import-section" id="importSection">
      <h3>📥 Unggah File Excel/CSV/PDF</h3>

      <form id="importForm" onsubmit="handleImport(event)" method="POST">
          <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
              <label class="file-input-wrapper">
                  <input type="file" id="importFile" accept=".xlsx,.csv,.xls" required>
                  <span class="file-input-label">Pilih File</span>
              </label>
          </div>

          <div class="import-button-group">
              <button type="submit" class="btn btn-primary" id="uploadBtn">
                  ✓ Upload
              </button>
              <button type="button" class="btn" 
                      style="background: #e5e7eb; color: #6b7280;" 
                      onclick="closeImportModal()">
                  ✕ Batal
              </button>
          </div>
      </form>
    </div>

  </div>
</div>
@endsection

<script>
    function openImportModal() {
  document.getElementById('importModal').style.display = 'flex';
}

function closeImportModal() {
  document.getElementById('importModal').style.display = 'none';
}
</script>