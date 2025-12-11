<?php

namespace App\Helpers;

class Ghostscript {

    public static function compressPdfWindows($filePath, $outputPath)
    {        
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Input PDF file not found'], 404);
        }
        
        $outputFilePath = $outputPath;
        
        $ghostscriptPath = '"C:\Program Files\gs\gs10.05.1\bin\gswin64.exe"';
        
        $command = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile="%s" "%s"',
            $ghostscriptPath,
            $outputFilePath,
            $filePath
        );
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            \Log::error('Ghostscript command failed', [
                'command' => $command,
                'return_code' => $returnCode,
                'output' => $output
            ]);
            
            return response()->json([
                'error' => 'Failed to compress PDF',
                'details' => implode("\n", $output)
            ], 500);
        }
        
        if (!file_exists($outputFilePath)) {
            return response()->json(['error' => 'Compressed PDF was not created'], 500);
        }
        
        return response()->download($outputFilePath)->deleteFileAfterSend(true);
    }

    public static function compressPdfLinux($filePath, $outputPath)
    {
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Input PDF file not found'], 404);
        }
        
        $outputFilePath = $outputPath;
        
        $command = sprintf(
            'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
            escapeshellarg($outputFilePath),
            escapeshellarg($filePath)
        );
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            return response()->json([
                'error' => 'Failed to compress PDF',
                'details' => implode("\n", $output)
            ], 500);
        }
        
        if (!file_exists($outputFilePath)) {
            return response()->json(['error' => 'Compressed PDF was not created'], 500);
        }
        
        return response()->download($outputFilePath)->deleteFileAfterSend(true);
    }    
}