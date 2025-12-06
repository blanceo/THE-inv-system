import json

# Load your JSON file, the masterlist
with open("Lab_inventory_masterlist.json", "r", encoding="utf-8") as f:
    data = json.load(f)

# Names of sheets/rooms to skip as "Item" entries
rooms_to_skip = ["LABORATORY 1", "BIOLOGY & EARTH LIFE", "STORAGE ROOM", "CHEMICAL ROOM"]

cleaned = []
for entry in data:
    item_name = entry.get("Item", "").strip().upper()

    # Skip if blank or looks like a room header
    if not item_name:
        continue
    if item_name in rooms_to_skip or ("ROOM" in item_name and len(item_name.split()) <= 3):
        continue

    # Replace 'nan' or placeholder strings with empty strings
    for k, v in entry.items():
        if isinstance(v, str) and v.strip().lower() == "nan":
            entry[k] = ""

    cleaned.append(entry)

# Save the final cleaned JSON
with open("shs_lab_inventory_final.json", "w", encoding="utf-8") as f:
    json.dump(cleaned, f, indent=2, ensure_ascii=False)

# ✅ Output results
print(f"✅ Cleaned {len(cleaned)} valid entries and saved to 'shs_lab_inventory_final.json'")
print("\n🔍 Preview of first 5 entries:")
for i, e in enumerate(cleaned[:5], start=1):
    print(f"\n{i}. {e}")
