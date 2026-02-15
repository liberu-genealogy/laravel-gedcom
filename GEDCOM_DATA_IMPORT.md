# GEDCOM Data Import Reference

This document details all GEDCOM 5.5/5.5.1 data elements that are imported by laravel-gedcom from the php-gedcom library.

## Overview

The laravel-gedcom package imports GEDCOM files and stores them in a relational database structure. It uses the [liberu-genealogy/php-gedcom](https://github.com/liberu-genealogy/php-gedcom) library for parsing.

## Import Process

1. **Parse GEDCOM file** using php-gedcom Parser
2. **Import global records**: Media Objects → Submitters → Submissions → Notes → Repositories → Sources
3. **Import individuals** with events, attributes, and relationships
4. **Import families** with events and relationships
5. **Link relationships** between persons, families, sources, media, etc.

## Top-Level GEDCOM Records

### Header Record (HEAD)
- **Source**: Used during export only
- **GEDCOM Version**: Detected and logged
- **Character Set**: Handled by parser
- **Submitter**: Linked to SUBM record

### Individual Records (INDI)

Each individual is stored in the `people` table with the following data:

#### Basic Information
- `gid` - GEDCOM ID (e.g., @I1@)
- `uid` - Unique identifier (_UID tag or generated)
- `name` - Full name
- `givn` - Given name
- `surn` - Surname
- `npfx` - Name prefix (title)
- `spfx` - Surname prefix
- `nsfx` - Name suffix
- `nick` - Nickname
- `sex` - Sex (M/F)
- `type` - Name type

#### System Fields
- `rin` - Record ID Number (RIN tag)
- `rfn` - Permanent Record File Number (RFN tag)
- `afn` - Ancestral File Number (AFN tag)
- `resn` - Restriction notice (RESN tag)
- `chan` - Change date/time

#### Dates and Places
- `birthday` - Birth date (formatted)
- `birth_month` - Birth month
- `birth_year` - Birth year
- `birthday_dati` - Birth date (original GEDCOM format)
- `birthday_plac` - Birth place
- `deathday` - Death date
- `death_month` - Death month
- `death_year` - Death year
- `deathday_dati` - Death date (original GEDCOM format)
- `deathday_plac` - Death place
- `deathday_caus` - Death cause
- `burial_day` - Burial date
- `burial_month` - Burial month
- `burial_year` - Burial year
- `burial_day_dati` - Burial date (original)
- `burial_day_plac` - Burial place

### Individual Events (INDI.EVEN / INDI.ATTR)

Stored in `person_events` table:

#### Life Events (40+ supported)
- **ADOP** - Adoption (with adoptive family link)
- **BAPM** - Baptism
- **BARM** - Bar Mitzvah
- **BASM** - Bas Mitzvah
- **BIRT** - Birth (with birth family link)
- **BLES** - Blessing
- **BURI** - Burial
- **CAST** - Caste
- **CENS** - Census
- **CHR** - Christening (with christening family link)
- **CHRA** - Adult Christening
- **CONF** - Confirmation
- **CREM** - Cremation
- **DEAT** - Death
- **DSCR** - Physical Description
- **EDUC** - Education
- **EMIG** - Emigration
- **FCOM** - First Communion
- **GRAD** - Graduation
- **IDNO** - National ID Number
- **IMMI** - Immigration
- **NATI** - Nationality
- **NATU** - Naturalization
- **NCHI** - Number of Children
- **NMR** - Number of Marriages
- **OCCU** - Occupation
- **ORDN** - Ordination
- **PROB** - Probate
- **PROP** - Property
- **RELI** - Religion
- **RESI** - Residence
- **RETI** - Retirement
- **SSN** - Social Security Number
- **TITL** - Title
- **WILL** - Will
- **EVEN** - Generic Event

#### Event Details Captured
For each event, the following details are stored:
- `title` - Event type (class name)
- `type` - Event TYPE tag value
- `attr` - Attribute value
- `date` - Date (formatted)
- `converted_date` - Date as timestamp
- `plac` - Place
- `addr_id` - Address reference
- `phon` - Phone
- `caus` - Cause
- `age` - Age at event
- `agnc` - Responsible agency
- `adop` - Adoption type (for ADOP events)
- `adop_famc` - Adoptive family (for ADOP events)
- `birt_famc` - Birth family (for BIRT events)
- `chr_famc` - Christening family (for CHR events)

#### Associated Records
Each event can link to:
- **Sources** (SOUR) - via `source_refs` table
- **Media** (OBJE) - via `source_refs` table
- **Notes** (NOTE) - via `note_refs` table
- **Change dates** (CHAN) - via `chans` table

### LDS Ordinances (INDI.BAPL/CONL/ENDL/SLGC)

Stored in dedicated tables:
- `person_bapl` - Baptism (LDS)
- `person_conl` - Confirmation (LDS)
- `person_endl` - Endowment (LDS)
- `person_slgc` - Sealing to Child (LDS)

### Individual Name Variants

Stored in `person_name_*` tables:

#### Name Records (person_names)
- Name with all components (npfx, givn, nick, spfx, surn, nsfx)
- Name type
- Linked to person via `person_id`

#### Phonetic Name Variants (person_name_fones)
- FONE tags - phonetic variations
- Linked to name record

#### Romanized Name Variants (person_name_romns)
- ROMN tags - romanized variations
- Linked to name record

### Individual Relationships

#### Family Links
- **FAMS** - Families where individual is spouse (stored in `families` table)
- **FAMC** - Family where individual is child (stored in `families` table)
  - With pedigree type (adopted, birth, foster, sealed)

#### Other Associations
- **ALIA** - Aliases (person_alias table)
- **ASSO** - Associates (person_asso table)
- **ANCI** - Ancestor interest (person_anci table)
- **DESI** - Descendant interest (person_desi table)
- **SUBM** - Submitter links (person_subm table)

### Family Records (FAM)

Stored in `families` table:
- `husband_id` - Reference to person (husband)
- `wife_id` - Reference to person (wife)
- `child_id` - Child references (via separate relationships)
- Family events (stored in `family_events`)

### Family Events (FAM.EVEN)

Stored in `family_events` table:

#### Family Event Types (11+ supported)
- **ANUL** - Annulment
- **CENS** - Census
- **DIV** - Divorce
- **DIVF** - Divorce Filed
- **ENGA** - Engagement
- **MARR** - Marriage
- **MARB** - Marriage Banns
- **MARC** - Marriage Contract
- **MARL** - Marriage License
- **MARS** - Marriage Settlement
- **EVEN** - Generic Event

#### Family Event Details
- `family_id` - Reference to family
- `title` - Event type
- `type` - Event TYPE tag
- `date` - Event date
- `converted_date` - Timestamp
- `plac` - Place
- `addr_id` - Address reference
- `phon` - Phone
- `caus` - Cause
- `age` - Age (stored on person record)
- `agnc` - Agency
- `husb` - Husband person ID
- `wife` - Wife person ID

#### LDS Family Ordinance
- **SLGS** - Sealing to Spouse (stored in `family_slgs` table)

### Source Records (SOUR)

Stored in `sources` table:
- Source ID
- Title
- Author
- Publication facts
- Text from source
- Repositories (via `source_repos`)
- Data fields (via `source_data` table)

### Repository Records (REPO)

Stored in `repositories` table:
- Repository name
- Address
- Notes
- Call numbers (via `source_repo` linking table)

### Media Object Records (OBJE)

Stored in `media_objects` table:
- Media title
- Media type
- Files (via `media_object_files` table)
  - File path
  - Format
  - Media type

### Note Records (NOTE)

Stored in `notes` table:
- Note text (can be multi-line)
- Linked to individuals, families, events, sources via `note_refs` table

### Submitter Records (SUBM)

Stored in `subms` table:
- Submitter name
- Address
- Phone
- Email
- Language preferences

### Submission Records (SUBN)

Stored in `subns` table:
- Submission name
- Family file name
- Temple code
- Generations count

## Performance Optimizations

### Batch Processing
- Events processed in batches of 100
- Persons upserted in batches of 1000
- Families processed in batches of 50

### Memory Management
- Query logging disabled during import
- Garbage collection called after major sections
- Progress tracking with minimal memory footprint

### Database Indexes
- `people.uid` - indexed for fast lookups
- `people.gid` - indexed for import mapping
- `person_events.[id, person_id, addr_id]` - composite index
- `family_events.[family_id, title]` - composite index
- Foreign key relationships for data integrity

## GEDCOM Tags Not Fully Implemented

The following GEDCOM tags are recognized but have minimal or no processing:

### Individual Level
- **FACT** - Generic fact (stored as EVEN)
- **NON-STANDARD TAGS** - Custom tags may not be imported

### Header Level
- **COPR** - Copyright
- **LANG** - Language
- **PLAC.FORM** - Place hierarchy format (not enforced)

### Multimedia
- **BLOB** - Binary large object (deprecated in 5.5.1, not imported)

## Migration Order

When setting up a new database, run migrations in this order:
1. Create core tables (people, families)
2. Create event tables (person_events, family_events)
3. Create relationship tables (person_alia, person_asso, etc.)
4. Create source/media tables
5. Add indexes (performance optimizations)
6. Add new columns (chr_famc, family event columns)

## Best Practices

1. **Large Files**: For GEDCOM files > 10MB, consider increasing PHP memory limit
2. **Progress Tracking**: Use `progressBar` parameter for CLI imports
3. **Database Connection**: Specify connection name for multi-tenant setups
4. **Error Handling**: Check logs for import warnings/errors
5. **Validation**: Run data validation after import to check relationships

## Version Compatibility

- **GEDCOM 5.5**: Fully supported
- **GEDCOM 5.5.1**: Fully supported (includes _UID tags)
- **GEDCOM 7.0**: Partial support via php-gedcom library
- **GEDCOM X**: Supported via separate GedcomXParser

## See Also

- [php-gedcom documentation](https://github.com/liberu-genealogy/php-gedcom)
- [GEDCOM 5.5.1 Standard](https://www.gedcom.org/gedcom.html)
- Main README.md for usage examples
