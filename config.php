<?php
// Update these settings to match your MySQL setup
const DB_HOST = 'localhost';
const DB_NAME = 'file_system';
const DB_USER = 'root';
const DB_PASS = '';

// Directory where uploaded files will be stored
const UPLOAD_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

// Max upload size in bytes (10 MB by default)
const MAX_UPLOAD_BYTES = 10 * 1024 * 1024;

// Allowed mime types (extend as needed)
const ALLOWED_MIME_TYPES = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'text/plain',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
    'application/msword', // doc
];

?>