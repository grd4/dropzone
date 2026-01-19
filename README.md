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

Your CSV/Excel file should include these columns for prefilling:

| Column Name | Description |
|-------------|-------------|
| `ApplicationName` | Name of the application |
| `VendorName` | Vendor or manufacturer |
| `HospitalSites` | Sites where app is deployed |
| `RecordedIntegrationPriority` | Current priority classification |

**Example:**
```csv
ApplicationName,VendorName,HospitalSites,RecordedIntegrationPriority
Epic MyChart,Epic Systems,Memorial Hospital,Go-Live Critical
Lab System,Cerner,All Sites,Post-Go-Live (with workarounds)
```

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
