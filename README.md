# HSA-048 RVF App Owner Interview Form

A web-based interview form with branching logic and spreadsheet prefill capabilities for RVF migration interviews.

## Features

✅ **Spreadsheet Upload** - Upload CSV or Excel files with application data
✅ **Application Picker** - Select which application to interview from your spreadsheet
✅ **Auto-Prefill** - Form automatically fills with data from your spreadsheet
✅ **Branching Logic** - Questions appear/hide based on previous answers
✅ **Progress Tracking** - Visual progress bar shows completion status
✅ **Validation** - Ensures all required fields are completed
✅ **Export Results** - Download completed interviews as JSON

## Quick Start

1. **Open the form**
   - Double-click `interview-form.html` in your browser
   - Or drag it into a browser window

2. **Upload your spreadsheet**
   - Click "Choose File" or drag & drop your CSV/Excel file
   - Use `sample-applications.csv` to test

3. **Select an application**
   - Choose from the dropdown list
   - Click "Load & Start Interview"

4. **Complete the interview**
   - Form prefills with known data (highlighted in yellow)
   - Answer all questions
   - Watch for branching questions that appear based on your answers

5. **Export results**
   - Click "💾 Export Results"
   - JSON file downloads with all responses

## Spreadsheet Format

Your CSV/Excel file should include these columns for prefilling (matches standard application inventory format):

| Column Name | Description | Prefills Field |
|-------------|-------------|----------------|
| `Application Name` | Name of the application | Application Name |
| `Vendor Name` | Vendor or manufacturer | Vendor Name |
| `Site` | Sites where app is deployed | Hospital(s) / Site(s) |
| `Department` | Department(s) using the application | Department |
| `Application Owner` | Application owner contact | Application Owner |
| `Project Manager` | Project manager contact | Project Manager |
| `Business Owner` | Business owner contact | Business Owner |
| `Application Status` | Current status | Application Status |
| `Integration Priority` | Current priority classification | Integration Priority |
| `Classification` | Type of application | (reference only) |
| `Description / Use Case` | What the app does | (reference only) |

**Example from your inventory:**
```csv
Application Name,Vendor Name,Site,Department,Application Owner,Project Manager,Business Owner,Application Status,Integration Priority
CARTS,AMN Healthcare,Glenwood,Multiple,Kalmaan May,,Ryan Haight,Active - Local Contract,Go-Live Critical
RALS,Abbott,Florida Medical; Glenwood,Lab,Scott Linthicum,Mike Moran,,Active - HSA Contract,Post-Go-Live (with workarounds)
```

**Note:** The form is flexible and will work with your existing spreadsheets even if some columns are missing. Empty columns won't affect the form functionality.

## Branching Logic

The form includes smart branching:

- **Web browser** → Shows URL and exposure questions
- **Fat client** → Shows workstation count and installation questions
- **Citrix/RDS** → Shows published app name
- **Kerberos = Yes** → Shows SPN list
- **Allow-listing** → Shows details field
- **Uses Rhapsody** → Shows interface route questions

## Export Format

Results export as JSON with:
- Form metadata (name, version, date)
- Original prefill data source
- All user responses

```json
{
  "form": "HSA-048 RVF App Owner Interview (v1)",
  "completedDate": "2026-01-19T...",
  "prefillSource": { ... },
  "responses": { ... }
}
```

## Technical Details

- **Single file** - No installation, no dependencies
- **Offline capable** - Works without internet (after initial load)
- **Browser compatible** - Chrome, Firefox, Safari, Edge
- **No data upload** - Everything stays local on your computer

## Customization

To modify the form questions or branching logic:

1. Open `interview-form.html` in a text editor
2. Find the `formDefinition` object in the `<script>` section
3. Modify sections, questions, or branching rules
4. Save and reload

## Support

- Check that your spreadsheet has the correct column names
- Prefill only works for the 4 supported fields
- Required fields are marked with a red asterisk (*)
- Progress bar shows completion percentage

## Files Included

- `interview-form.html` - Main application (self-contained)
- `sample-applications.csv` - Test data with 8 sample applications
- `README.md` - This documentation

---

**Version:** 1.0
**Last Updated:** 2026-01-19
