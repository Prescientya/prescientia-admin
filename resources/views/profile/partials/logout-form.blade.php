<div>
    <h6 class="mb-3"><strong>Logout Akun</strong></h6>
    <p class="text-muted mb-3">
        Setelah logout, Anda perlu login kembali untuk mengakses sistem.
    </p>

    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Apakah Anda yakin ingin logout?')">
            <i class="bi bi-box-arrow-right"></i>
            Logout Sekarang
        </button>
    </form>
</div>
