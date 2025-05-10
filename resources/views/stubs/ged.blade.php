@foreach($submissions as $submission)
0 @SUBM@ SUBM
1 NAME {{ $submission->name ?? 'Unknown' }}
@if($submission->addr_id)
1 ADDR
@if($submission->adr1)
2 ADR1 {{ $submission->adr1 }}
@endif
@if($submission->adr2)
2 ADR2 {{ $submission->adr2 }}
@endif
@if($submission->city)
2 CITY {{ $submission->city }}
@endif
@if($submission->stae)
2 STAE {{ $submission->stae }}
@endif
@if($submission->post)
2 POST {{ $submission->post }}
@endif
@if($submission->ctry)
2 CTRY {{ $submission->ctry }}
@endif
@endif
@if($submission->phon)
1 PHON {{ $submission->phon }}
@endif
@endforeach

@foreach($notes as $note)
0 @N{{ $note->id }}@ NOTE {{ $note->note }}
@endforeach

@foreach($repositories as $repo)
0 @R{{ $repo->id }}@ REPO
1 NAME {{ $repo->name ?? 'Unknown Repository' }}
@if($repo->addr_id)
1 ADDR
@if($repo->address && $repo->address->adr1)
2 ADR1 {{ $repo->address->adr1 }}
@endif
@if($repo->address && $repo->address->city)
2 CITY {{ $repo->address->city }}
@endif
@if($repo->address && $repo->address->stae)
2 STAE {{ $repo->address->stae }}
@endif
@if($repo->address && $repo->address->post)
2 POST {{ $repo->address->post }}
@endif
@if($repo->address && $repo->address->ctry)
2 CTRY {{ $repo->address->ctry }}
@endif
@endif
@endforeach

@foreach($sources as $source)
0 @S{{ $source->id }}@ SOUR
1 TITL {{ $source->titl ?? 'Unknown Source' }}
@if($source->auth)
1 AUTH {{ $source->auth }}
@endif
@if($source->publ)
1 PUBL {{ $source->publ }}
@endif
@if($source->repositories->count() > 0)
@foreach($source->repositories as $repo)
1 REPO @R{{ $repo->id }}@
@endforeach
@endif
@if($source->notes->count() > 0)
@foreach($source->notes as $note)
1 NOTE @N{{ $note->id }}@
@endforeach
@endif
@endforeach

@foreach($mediaObjects as $media)
0 @M{{ $media->id }}@ OBJE
1 TITL {{ $media->titl ?? 'Unknown Media' }}
@if($media->file)
1 FILE {{ $media->file }}
@if($media->form)
2 FORM {{ $media->form }}
@endif
@endif
@endforeach

@foreach($people as $person)
0 @I{{ $person->id }}@ INDI
@if($person->names->count() > 0)
@foreach($person->names as $name)
1 NAME {{ $name->name ?? $person->name ?? 'Unknown' }}
@if($name->givn)
2 GIVN {{ $name->givn }}
@endif
@if($name->surn)
2 SURN {{ $name->surn }}
@endif
@if($name->nick)
2 NICK {{ $name->nick }}
@endif
@endforeach
@else
1 NAME {{ $person->name ?? 'Unknown' }}
@if($person->givn)
2 GIVN {{ $person->givn }}
@endif
@if($person->surn)
2 SURN {{ $person->surn }}
@endif
@endif
@if($person->sex)
1 SEX {{ $person->sex }}
@endif
@if($person->birthday || $person->birth_year)
1 BIRT
@if($person->birthday)
2 DATE {{ strtoupper(Carbon\Carbon::parse($person->birthday)->format('d M Y')) }}
@elseif($person->birth_year)
2 DATE {{ $person->birth_year }}
@endif
@if($person->birth_place)
2 PLAC {{ $person->birth_place }}
@endif
@endif
@if($person->deathday || $person->death_year)
1 DEAT
@if($person->deathday)
2 DATE {{ strtoupper(Carbon\Carbon::parse($person->deathday)->format('d M Y')) }}
@elseif($person->death_year)
2 DATE {{ $person->death_year }}
@endif
@if($person->death_place)
2 PLAC {{ $person->death_place }}
@endif
@endif
@if($person->burial_day || $person->burial_year)
1 BURI
@if($person->burial_day)
2 DATE {{ strtoupper(Carbon\Carbon::parse($person->burial_day)->format('d M Y')) }}
@elseif($person->burial_year)
2 DATE {{ $person->burial_year }}
@endif
@if($person->burial_place)
2 PLAC {{ $person->burial_place }}
@endif
@endif
@if($person->events->count() > 0)
@foreach($person->events as $event)
1 {{ $event->type }}
@if($event->date)
2 DATE {{ strtoupper(Carbon\Carbon::parse($event->date)->format('d M Y')) }}
@endif
@if($event->place)
2 PLAC {{ $event->place }}
@endif
@endforeach
@endif
@if($person->child_in_family_id)
1 FAMC @F{{ $person->child_in_family_id }}@
@endif
@foreach($families as $family)
@if($family->husband_id == $person->id || $family->wife_id == $person->id)
1 FAMS @F{{ $family->id }}@
@endif
@endforeach
@if($person->notes->count() > 0)
@foreach($person->notes as $note)
1 NOTE @N{{ $note->id }}@
@endforeach
@endif
@if($person->sources->count() > 0)
@foreach($person->sources as $source)
1 SOUR @S{{ $source->id }}@
@endforeach
@endif
@if($person->media->count() > 0)
@foreach($person->media as $media)
1 OBJE @M{{ $media->id }}@
@endforeach
@endif
@endforeach

@foreach($families as $family)
0 @F{{ $family->id }}@ FAM
@if($family->husband_id)
1 HUSB @I{{ $family->husband_id }}@
@endif
@if($family->wife_id)
1 WIFE @I{{ $family->wife_id }}@
@endif
@foreach($people as $person)
@if($person->child_in_family_id == $family->id)
1 CHIL @I{{ $person->id }}@
@endif
@endforeach
@if($family->marr_date || $family->marr_plac)
1 MARR
@if($family->marr_date)
2 DATE {{ strtoupper(Carbon\Carbon::parse($family->marr_date)->format('d M Y')) }}
@endif
@if($family->marr_plac)
2 PLAC {{ $family->marr_plac }}
@endif
@endif
@if($family->div_date)
1 DIV
2 DATE {{ strtoupper(Carbon\Carbon::parse($family->div_date)->format('d M Y')) }}
@endif
@if($family->notes->count() > 0)
@foreach($family->notes as $note)
1 NOTE @N{{ $note->id }}@
@endforeach
@endif
@if($family->sources->count() > 0)
@foreach($family->sources as $source)
1 SOUR @S{{ $source->id }}@
@endforeach
@endif
@if($family->media->count() > 0)
@foreach($family->media as $media)
1 OBJE @M{{ $media->id }}@
@endforeach
@endif
@endforeach

0 TRLR