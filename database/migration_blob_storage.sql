-- Migration to add blob storage support for images
-- This migration adds blob columns and creates image serving functionality

-- Add blob columns to featured_images table
ALTER TABLE featured_images 
ADD COLUMN image_blob LONGBLOB AFTER image_path,
ADD COLUMN image_mime_type VARCHAR(50) AFTER image_blob,
ADD COLUMN image_size INT AFTER image_mime_type,
ADD COLUMN original_filename VARCHAR(255) AFTER image_size;

-- Add blob columns to content table for featured images
ALTER TABLE content 
ADD COLUMN featured_image_blob LONGBLOB AFTER featured_image,
ADD COLUMN featured_image_mime_type VARCHAR(50) AFTER featured_image_blob,
ADD COLUMN featured_image_size INT AFTER featured_image_mime_type,
ADD COLUMN featured_image_filename VARCHAR(255) AFTER featured_image_size;

-- Add blob columns to events table for featured images  
ALTER TABLE events 
ADD COLUMN featured_image_blob LONGBLOB AFTER featured_image,
ADD COLUMN featured_image_mime_type VARCHAR(50) AFTER featured_image_blob,
ADD COLUMN featured_image_size INT AFTER featured_image_mime_type,
ADD COLUMN featured_image_filename VARCHAR(255) AFTER featured_image_size;

-- Create indexes for blob-enabled image serving
CREATE INDEX idx_featured_images_blob ON featured_images(image_blob(1));
CREATE INDEX idx_content_featured_blob ON content(featured_image_blob(1));
CREATE INDEX idx_events_featured_blob ON events(featured_image_blob(1));

-- Add constraints for image types
ALTER TABLE featured_images 
ADD CONSTRAINT chk_image_mime_type 
CHECK (image_mime_type IN ('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'));

ALTER TABLE content 
ADD CONSTRAINT chk_featured_image_mime_type 
CHECK (featured_image_mime_type IN ('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'));

ALTER TABLE events 
ADD CONSTRAINT chk_events_featured_image_mime_type 
CHECK (featured_image_mime_type IN ('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'));

-- Create a function to update blob data when file paths are updated
DELIMITER $$

CREATE TRIGGER update_featured_images_blob 
BEFORE UPDATE ON featured_images
FOR EACH ROW
BEGIN
    -- If image_path changes and image_blob is NULL, we'll handle this in PHP
    IF NEW.image_path != OLD.image_path AND NEW.image_blob IS NULL THEN
        SET NEW.image_blob = NULL; -- This will be populated by PHP migration script
    END IF;
END$$

DELIMITER ;