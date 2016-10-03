ALTER TABLE Event CHANGE presenter presenter1 VARCHAR(255) NULL;
ALTER TABLE Event CHANGE presenter_avatar presenter1_avatar VARCHAR(255) NULL;
ALTER TABLE Event ADD presenter2 VARCHAR(255) NULL;
ALTER TABLE Event ADD presenter2_avatar VARCHAR(255) NULL;
ALTER TABLE Event ADD presenter3 VARCHAR(255) NULL;
ALTER TABLE Event ADD presenter3_avatar VARCHAR(255) NULL;
