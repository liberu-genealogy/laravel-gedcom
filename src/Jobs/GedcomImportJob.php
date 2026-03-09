<?php

namespace FamilyTree365\LaravelGedcom\Jobs;

use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GedcomImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $conn,
        public readonly string $filename,
        public readonly string $slug,
    ) {
    }

    public function handle(): void
    {
        GedcomParserFacade::parse($this->conn, $this->filename, $this->slug, true);
    }
}
