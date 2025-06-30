<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    private $backupPath;

    public function __construct()
    {
        // Tentukan path penyimpanan backup. Pastikan direktori ini ada dan bisa ditulis oleh server.
        $this->backupPath = storage_path('app/backups');
    }

    /**
     * Menampilkan halaman backup dan daftar file backup yang ada.
     */
    public function index()
    {
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath);
        }

        $backupFiles = collect(File::files($this->backupPath))
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            });

        return view('admin.backups.index', compact('backupFiles'));
    }

    /**
     * Membuat file backup database baru.
     */
    public function create()
    {
        try {
            // Ambil konfigurasi database dari environment Laravel
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');

            // Buat nama file dengan timestamp
            $fileName = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql';
            $filePath = $this->backupPath . '/' . $fileName;

            // Buat perintah mysqldump
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbHost),
                escapeshellarg($dbName),
                escapeshellarg($filePath)
            );

            // Jalankan perintah
            $process = Process::fromShellCommandline($command);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return redirect()->route('admin.backups.index')->with('success', 'Backup database berhasil dibuat!');

        } catch (ProcessFailedException $exception) {
            // Hapus file yang mungkin gagal dibuat
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            return redirect()->route('admin.backups.index')->with('error', 'Gagal membuat backup: ' . $exception->getMessage());
        }
    }

    /**
     * Mengunduh file backup.
     */
    public function download($filename)
    {
        $filePath = $this->backupPath . '/' . $filename;

        if (!File::exists($filePath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return response()->download($filePath);
    }

    /**
     * Menghapus file backup.
     */
    public function destroy($filename)
    {
        $filePath = $this->backupPath . '/' . $filename;

        Log::create([
            'user_id'     => Auth::id(),
            'action'      => 'HAPUS_BACKUP',
            'description' => 'Pengguna ' . Auth::user()->username . ' menghapus file backup: ' . $filename
        ]);

        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('admin.backups.index')->with('success', 'File backup berhasil dihapus.');
        }

        return redirect()->route('admin.backups.index')->with('error', 'File backup tidak ditemukan.');
    }
}