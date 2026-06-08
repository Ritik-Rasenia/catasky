import os
import re

files_to_update = [
    "resources/views/layouts/frontend.blade.php",
    "resources/views/welcome.blade.php",
    "resources/views/c_catalogue.blade.php",
    "resources/views/pricing.blade.php",
    "resources/views/contact.blade.php",
    "resources/views/search-results.blade.php",
    "resources/views/store-contact.blade.php",
    "resources/views/category-products.blade.php",
    "resources/views/product-details.blade.php"
]

replacements = {
    "catalogues": "catalogs",
    "Catalogues": "Catalogs",
    "catalogue": "catalog",
    "Catalogue": "Catalog",
    "CATALOGUE": "CATALOG"
}

def should_skip(content, match):
    start, end = match.span()
    # Check if preceded by route(' or route(" or c_ or generatePDF
    preceding = content[max(0, start - 15):start]
    if "route('" in preceding or 'route("' in preceding or preceding.endswith("c_") or preceding.endswith("generatePDF"):
        return True
    # Check if followed by _code or _urls or similar database fields
    following = content[end:end + 10]
    if following.startswith("_code") or following.startswith("_urls") or following.startswith("_path"):
        return True
    return False

for rel_path in files_to_update:
    path = os.path.join("c:\\xampp\\htdocs\\catasky", rel_path)
    if not os.path.exists(path):
        print(f"File not found: {path}")
        continue
        
    with open(path, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        
    original = content
    
    # We will find matches of \b(catalogue|Catalogue|CATALOGUE|catalogues|Catalogues)\b
    # and replace them if they don't meet the skip criteria
    pattern = re.compile(r'\b(catalogues|Catalogues|catalogue|Catalogue|CATALOGUE)\b')
    
    offset = 0
    for match in pattern.finditer(original):
        if should_skip(original, match):
            continue
        old_val = match.group(1)
        new_val = replacements[old_val]
        
        # Calculate new positions in the modified content
        start = match.start() + offset
        end = match.end() + offset
        
        content = content[:start] + new_val + content[end:]
        offset += len(new_val) - len(old_val)
        
    if content != original:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated: {rel_path}")
    else:
        print(f"No changes: {rel_path}")
