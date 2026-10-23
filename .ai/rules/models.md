---
paths:
  - 'app/Models/**'
---

# Models

## Files are owned through HasFiles collections
A model that owns files uses the HasFiles trait and implements HasFilesInterface, and declares each collection in fileCollections() (single, mimes, max, visibility). Read a collection with a relation named after it that returns fileOf(name). Save and delete with saveFile() / deleteFile(), never with Storage by hand, and build the validation of the upload with Model::fileRules(name). Only the path and the disk are stored: the url is calculated, and the disk comes from config(filesystems.files_disk). Adding files to another model is declaring a collection, not writing a new File flow.
