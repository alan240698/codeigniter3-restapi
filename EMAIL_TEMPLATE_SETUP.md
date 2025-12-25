# Email Template System - Setup Instructions

## Database Setup

### Step 1: Run the SQL Migration

The database name is: **archetype_intranet**

You need to execute the SQL migration file to create the required tables.

**Option 1: Using phpMyAdmin (Recommended for Laragon)**
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select database: `archetype_intranet`
3. Click on "SQL" tab
4. Copy and paste the contents of: `database/migrations/create_email_templates_tables.sql`
5. Click "Go" to execute

**Option 2: Using Laragon MySQL Console**
1. Open Laragon
2. Click "Menu" → "MySQL" → "MySQL Console"
3. Run the following commands:
```sql
USE archetype_intranet;
SOURCE C:/laragon/www/codeigniter3-restapi/database/migrations/create_email_templates_tables.sql;
```

**Option 3: Using Command Line (if mysql is in PATH)**
```bash
cd c:\laragon\www\codeigniter3-restapi
mysql -u root archetype_intranet < database/migrations/create_email_templates_tables.sql
```

### Step 2: Verify Tables Created

Run this query to verify:
```sql
USE archetype_intranet;
SHOW TABLES LIKE 'it_ticket_email%';
```

You should see 3 tables:
- `it_ticket_email_templates`
- `it_ticket_template_instances`
- `it_ticket_template_actions`

## Testing the System

### 1. Test the Templates Tab UI

1. Navigate to: http://localhost/codeigniter3-restapi/it-ticket
2. Click on the "Templates" tab
3. You should see 2 sample templates already loaded (from SQL migration)
4. Try creating a new template:
   - Click "Add Template"
   - Fill in the form
   - Click "Save"

### 2. Test Template Preview

1. Edit any template
2. Click "Update Preview" button
3. Verify that variables are replaced with sample data

### 3. Test Standalone Template Rendering

**Option A: Using the API to create an instance**

Use Postman or similar tool:
```
POST http://localhost/codeigniter3-restapi/email-templates/create-instance
Content-Type: application/json

{
  "template_id": 1,
  "recipient_email": "test@example.com"
}
```

Response will include a `url` field. Open that URL in a browser.

**Option B: Direct database insert (for testing)**
```sql
INSERT INTO it_ticket_template_instances 
  (template_id, token, rendered_subject, rendered_body, context_data, expires_at)
VALUES 
  (1, 'TEST_TOKEN_123', 'Test Subject', '<h1>Test Body</h1>', '{}', DATE_ADD(NOW(), INTERVAL 30 DAY));
```

Then visit: http://localhost/codeigniter3-restapi/template/view/TEST_TOKEN_123

### 4. Test Action Buttons

1. Open a template instance URL (from step 3)
2. Click "Approve" or "Reject" button
3. Verify action is logged
4. Try clicking again - should show "Action Already Performed"

## API Endpoints Reference

All endpoints use base URL: `http://localhost/codeigniter3-restapi/`

### Template Management
- `GET /email-templates` - List all templates
- `GET /email-templates/show/{id}` - Get single template
- `POST /email-templates/store` - Create template
- `POST /email-templates/update/{id}` - Update template
- `POST /email-templates/delete/{id}` - Delete template
- `GET /email-templates/render/{id}` - Preview template with sample data

### Template Instances
- `POST /email-templates/create-instance` - Create instance with token

### Public Access (No Auth)
- `GET /template/view/{token}` - View rendered template
- `POST /template/action/{token}` - Perform action (approve/reject)

## Troubleshooting

### Issue: Templates tab shows "Error loading templates"
- Check that database tables are created
- Verify database connection in `application/config/database.php`
- Check browser console for JavaScript errors

### Issue: "Template not found" when viewing instance
- Verify token exists in `it_ticket_template_instances` table
- Check that token hasn't expired

### Issue: Actions not working
- Check browser console for errors
- Verify `TemplateViewController` is accessible
- Check that routes are configured correctly

## Next Steps

1. **Integrate with Ticket System**: Modify ticket creation/update logic to send emails using templates
2. **Email Sending**: Add actual email sending functionality (currently only creates instances)
3. **Custom Actions**: Extend `_process_action()` in `TemplateViewController` to handle specific business logic
4. **Permissions**: Add authentication/authorization for template management endpoints
