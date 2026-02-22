#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Auto-replace alert() and confirm() with SweetAlert2 - IMPROVED VERSION
For IT Ticket System
"""

import re
import sys
from pathlib import Path

# Set UTF-8 encoding for Windows console
if sys.platform == 'win32':
    import codecs
    sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')
    sys.stderr = codecs.getwriter('utf-8')(sys.stderr.buffer, 'strict')

# Files to process
FILES = [
    r'application\views\it_ticket\tabs\workflows.php',
    r'application\views\it_ticket\tabs\teams.php',
    r'application\views\it_ticket\tabs\templates.php',
    r'application\views\it_ticket\tabs\sla.php',
    r'application\views\it_ticket\tabs\rules.php',
    r'application\views\it_ticket\tabs\services.php',
    r'application\views\it_ticket\tabs\monitoring.php',
]

BASE_DIR = Path(__file__).parent

def replace_alerts(content):
    """Replace all alert() calls with SweetAlert2"""
    
    # Pattern 1: Conditional alerts with ternary operator
    # alert(id ? 'Updated successfully' : 'Created successfully');
    pattern1 = r"alert\((\w+)\s*\?\s*'([^']+)'\s*:\s*'([^']+)'\);"
    def repl1(match):
        var = match.group(1)
        msg1 = match.group(2)
        msg2 = match.group(3)
        return f"ITTicketUI.showSuccess({var} ? '{msg1}' : '{msg2}');"
    content = re.sub(pattern1, repl1, content)
    
    # Pattern 2: Success messages with callback (closeModal + load)
    # alert('... successfully'); this.closeModal(); this.loadXXX();
    pattern2 = r"alert\('([^']+successfully)'\);\s*this\.closeModal\(\);\s*this\.load(\w+)\(\);"
    replacement2 = r"ITTicketUI.showSuccess('\1', () => { this.closeModal(); this.load\2(); });"
    content = re.sub(pattern2, replacement2, content)
    
    # Pattern 3: Success messages with single callback
    # alert('... successfully'); this.loadXXX();
    pattern3 = r"alert\('([^']+successfully)'\);\s*this\.load(\w+)\(\);"
    replacement3 = r"ITTicketUI.showSuccess('\1', () => this.load\2());"
    content = re.sub(pattern3, replacement3, content)
    
    # Pattern 4: Simple success messages
    # alert('... successfully');
    pattern4 = r"alert\('([^']+successfully)'\);"
    replacement4 = r"ITTicketUI.showSuccess('\1');"
    content = re.sub(pattern4, replacement4, content)
    
    # Pattern 5: Error messages starting with "Error"
    # alert('Error ...');
    pattern5 = r"alert\('(Error [^']+)'\);"
    replacement5 = r"ITTicketUI.showError('\1');"
    content = re.sub(pattern5, replacement5, content)
    
    # Pattern 6: Validation messages starting with "Please"
    # alert('Please ...');
    pattern6 = r"alert\('(Please [^']+)'\);"
    replacement6 = r"ITTicketUI.showValidationError('\1');"
    content = re.sub(pattern6, replacement6, content)
    
    # Pattern 7: Validation messages starting with "Invalid"
    # alert('Invalid ...');
    pattern7 = r"alert\('(Invalid [^']+)'\);"
    replacement7 = r"ITTicketUI.showValidationError('\1');"
    content = re.sub(pattern7, replacement7, content)
    
    # Pattern 8: Other error messages (data.message)
    # alert(data.message || 'Error ...');
    pattern8 = r"alert\(data\.message \|\| '([^']+)'\);"
    replacement8 = r"ITTicketUI.showError(data.message || '\1');"
    content = re.sub(pattern8, replacement8, content)
    
    # Pattern 9: Generic alerts (remaining)
    # alert('...');
    pattern9 = r"alert\('([^']+)'\);"
    replacement9 = r"ITTicketUI.showError('\1');"
    content = re.sub(pattern9, replacement9, content)
    
    return content

def replace_confirms(content):
    """Replace all confirm() calls with SweetAlert2"""
    
    # Pattern: if (!confirm('...')) return;
    pattern = r"if \(!confirm\('([^']+)'\)\) return;"
    replacement = r"const confirmed = await ITTicketUI.confirm('Confirm Action', '\1', 'Yes, proceed'); if (!confirmed) return;"
    content = re.sub(pattern, replacement, content)
    
    return content

def make_functions_async(content):
    """Make functions async if they use await"""
    
    functions_to_async = [
        'saveWorkflow', 'deleteWorkflow', 'editWorkflow',
        'saveState', 'deleteState', 'editState',
        'saveTransition', 'deleteTransition', 'editTransition',
        'saveTeam', 'deleteTeam', 'editTeam',
        'saveMember', 'deleteMember', 'editMember',
        'saveTemplate', 'deleteTemplate', 'editTemplate',
        'saveSLA', 'deleteSLA', 'editSLA',
        'saveRule', 'deleteRule', 'editRule',
        'saveServiceGroup', 'deleteServiceGroup', 'editServiceGroup',
        'saveTicketType', 'deleteTicketType', 'editTicketType',
        'saveIssueType', 'deleteIssueType', 'editIssueType',
    ]
    
    for func in functions_to_async:
        # Pattern: funcName() {
        pattern = rf'(\s+)({func})\s*\('
        # Check if not already async
        if f'async {func}' not in content:
            replacement = rf'\1async \2('
            content = re.sub(pattern, replacement, content)
    
    return content

def process_file(filepath):
    """Process a single file"""
    full_path = BASE_DIR / filepath
    
    if not full_path.exists():
        print(f"WARNING: File not found: {filepath}")
        return False
    
    print(f"Processing: {filepath}")
    
    # Read file
    with open(full_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original_content = content
    
    # Apply replacements
    content = replace_alerts(content)
    content = replace_confirms(content)
    content = make_functions_async(content)
    
    # Count changes
    alert_count = len(re.findall(r"ITTicketUI\.show", content)) - len(re.findall(r"ITTicketUI\.show", original_content))
    confirm_count = len(re.findall(r"ITTicketUI\.confirm", content)) - len(re.findall(r"ITTicketUI\.confirm", original_content))
    
    if content != original_content:
        # Write new content
        with open(full_path, 'w', encoding='utf-8') as f:
            f.write(content)
        
        print(f"   SUCCESS: Replaced {alert_count} alerts and {confirm_count} confirms")
        return True
    else:
        print(f"   INFO: No changes needed")
        return False

def main():
    """Main function"""
    print("=" * 60)
    print("Auto-replacing alert() and confirm() with SweetAlert2")
    print("IMPROVED VERSION - Catches all patterns")
    print("=" * 60)
    print()
    
    processed = 0
    modified = 0
    
    for filepath in FILES:
        if process_file(filepath):
            modified += 1
        processed += 1
        print()
    
    print("=" * 60)
    print("Complete!")
    print(f"   Processed: {processed} files")
    print(f"   Modified: {modified} files")
    print("=" * 60)
    print()
    print("Next step: Hard refresh browser (Ctrl+Shift+R)")
    print()

if __name__ == '__main__':
    main()
