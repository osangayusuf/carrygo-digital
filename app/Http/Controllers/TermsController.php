<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TermsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Terms/Index', [
            'documents' => $this->getDocuments(),
        ]);
    }

    public function show(string $slug): Response
    {
        $documents = $this->getDocuments();
        $doc = collect($documents)->firstWhere('slug', $slug);

        if (! $doc) {
            abort(404, 'Terms document not found.');
        }

        $filePath = public_path('terms/'.$doc['filename']);
        $blocks = $this->parseDocxContent($filePath);

        return Inertia::render('Terms/Show', [
            'current_document' => [
                'name' => $doc['name'],
                'slug' => $doc['slug'],
                'filename' => $doc['filename'],
                'blocks' => $blocks,
            ],
            'documents' => $documents,
        ]);
    }

    public function accept(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && ! $user->hasAcceptedTerms()) {
            $user->update([
                'terms_accepted_at' => now(),
            ]);
        }

        return back()->with('success', 'Terms and Conditions accepted successfully.');
    }

    private function getDocuments(): array
    {
        $termsPath = public_path('terms');
        $documents = [];

        if (File::isDirectory($termsPath)) {
            $files = File::files($termsPath);
            foreach ($files as $file) {
                if (in_array(strtolower($file->getExtension()), ['docx', 'pdf', 'doc', 'txt', 'md'])) {
                    $filename = $file->getFilename();
                    $name = pathinfo($filename, PATHINFO_FILENAME);
                    $slug = Str::slug($name);
                    $documents[] = [
                        'name' => $name,
                        'slug' => $slug,
                        'filename' => $filename,
                        'url' => route('terms.show', $slug),
                        'download_url' => asset('terms/'.rawurlencode($filename)),
                    ];
                }
            }
        }

        return $documents;
    }

    private function parseDocxContent(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [];
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $xmlContent) {
            return [];
        }

        $dom = new \DOMDocument;
        @$dom->loadXML($xmlContent);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $elements = $xpath->query('//w:body/*[self::w:p or self::w:tbl]');
        $blocks = [];

        foreach ($elements as $node) {
            if ($node->nodeName === 'w:p') {
                $text = '';
                $texts = $xpath->query('.//w:t', $node);
                foreach ($texts as $t) {
                    $text .= $t->nodeValue;
                }
                $text = trim($text);
                if ($text !== '') {
                    $blocks[] = [
                        'type' => 'paragraph',
                        'text' => $text,
                    ];
                }
            } elseif ($node->nodeName === 'w:tbl') {
                $trList = $xpath->query('./w:tr', $node);
                $headers = [];
                $rows = [];

                foreach ($trList as $rIndex => $tr) {
                    $tcList = $xpath->query('./w:tc', $tr);
                    $rowCells = [];

                    foreach ($tcList as $tc) {
                        $cellText = '';
                        $texts = $xpath->query('.//w:t', $tc);
                        foreach ($texts as $t) {
                            $cellText .= $t->nodeValue;
                        }
                        $rowCells[] = trim($cellText);
                    }

                    if ($rIndex === 0) {
                        $headers = $rowCells;
                    } else {
                        $rows[] = $rowCells;
                    }
                }

                if (count($headers) > 0 || count($rows) > 0) {
                    $blocks[] = [
                        'type' => 'table',
                        'headers' => $headers,
                        'rows' => $rows,
                    ];
                }
            }
        }

        return $blocks;
    }
}
