---
paths:
  - 'resources/js/Components/Forms/**'
---

# Forms

## A form that uploads a file to an update is sent as a POST with _method put
PHP does not read files from a PUT request and Inertia 3 does not spoof the method: when a form sends a File to an update, use form.transform((data) => ({ ...data, _method: "put" })).post(url) instead of form.put(url). See UserForm.vue. Files are chosen with the iImage input (v-model for the File, v-model:removed to delete the current one) and shown with AppAvatar.
