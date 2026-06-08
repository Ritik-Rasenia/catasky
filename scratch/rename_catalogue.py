import os

directories = ["app", "config", "database", "resources", "routes", "tests"]

replacements = [
    ('catalogues', 'catalogs'),
    ('Catalogues', 'Catalogs'),
    ('CATALOGUES', 'CATALOGS'),
    ('catalogue', 'catalog'),
    ('Catalogue', 'Catalog'),
    ('CATALOGUE', 'CATALOG'),
]

def replace_in_file(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading {file_path}: {e}")
        return

    original = content
    for old_str, new_str in replacements:
        content = content.replace(old_str, new_str)

    if content != original:
        try:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated content of {file_path}")
        except Exception as e:
            print(f"Error writing {file_path}: {e}")

def rename_files(start_dir):
    for root, dirs, files in os.walk(start_dir, topdown=False):
        for name in files:
            if any(rep[0].lower() in name.lower() for rep in replacements):
                new_name = name
                # Apply replacements in reverse order or longest first
                # Actually, replacements has catalogues before catalogue, so it's correct
                for old_str, new_str in replacements:
                    new_name = new_name.replace(old_str, new_str)
                if new_name != name:
                    old_path = os.path.join(root, name)
                    new_path = os.path.join(root, new_name)
                    try:
                        os.rename(old_path, new_path)
                        print(f"Renamed file: {old_path} -> {new_path}")
                    except Exception as e:
                        print(f"Error renaming file {old_path}: {e}")

if __name__ == "__main__":
    # 1. Replace content in all files
    for directory in directories:
        dir_path = os.path.join("c:\\xampp\\htdocs\\catasky", directory)
        for root, dirs, files in os.walk(dir_path):
            for file in files:
                file_path = os.path.join(root, file)
                replace_in_file(file_path)

    # 2. Rename files
    for directory in directories:
        dir_path = os.path.join("c:\\xampp\\htdocs\\catasky", directory)
        rename_files(dir_path)

    print("Find & Replace completed!")
