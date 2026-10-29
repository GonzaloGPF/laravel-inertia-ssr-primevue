---
paths:
  - 'app/Models/**'
---

# Models

## Files are owned through HasFiles collections
A model that owns files uses the HasFiles trait and implements HasFilesInterface, and declares each collection in fileCollections() (single, mimes, max, visibility). Read a collection with a relation named after it that returns fileOf(name). Save and delete with saveFile() / deleteFile(), never with Storage by hand, and build the validation of the upload with Model::fileRules(name). Only the path and the disk are stored: the url is calculated, and the disk comes from config(filesystems.files_disk). Adding files to another model is declaring a collection, not writing a new File flow.

## Models have a ulid as their id
The primary key of a model is a ulid, named id as always: the table declares it with $table->ulid('id')->primary(), the foreign keys to it with foreignUlid() (or ulidMorphs() for the polymorphic ones), and the model uses HasUlids. Never use id() or foreignId() for them. That way the ids can be exposed (routes, props, filters, urls) without anybody counting or walking through the records, and there is no second key to translate: user.id is the same in the database, the url and the frontend. An id that is not a ulid is a 404 in the routes. Ranges (min_id, max_id) make no sense on them. Ids are not access control: every endpoint still goes through its policy.
