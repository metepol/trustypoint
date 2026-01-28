CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    content TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE treatments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL,
    summary TEXT,
    content TEXT,
    image_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    treatment_id INT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE SET NULL
);

INSERT INTO pages (slug, title, excerpt, content) VALUES
('about', 'About', 'We deliver holistic care for hair, aesthetics, and dental health.', 'TrustyPoint provides a premium patient journey with specialist physicians, modern facilities, and multilingual care teams.'),
('contact', 'Contact', 'Reach us for consultation scheduling and travel support.', 'Phone: +90 212 000 00 00\nEmail: info@trustypoint.com\nAddress: Istanbul, Turkey'),
('treatments', 'Treatments', 'Discover our curated portfolio of advanced treatments.', 'Choose from hair restoration, medical aesthetics, and dental solutions tailored to your needs.');

INSERT INTO treatments (title, slug, category, summary, content, image_url) VALUES
('FUE Hair Transplant', 'fue-hair-transplant', 'Hair Treatments', 'Minimally invasive follicular unit extraction with natural hairline design.', 'Our FUE procedure combines precision extraction with custom graft placement to deliver natural density and rapid recovery.', 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1200&q=80'),
('DHI Hair Transplant', 'dhi-hair-transplant', 'Hair Treatments', 'Direct implantation technique for controlled density and angle.', 'DHI enables maximum control of graft direction and density for a refined hairline.', 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80'),
('Medical Skin Rejuvenation', 'medical-skin-rejuvenation', 'Medical Aesthetics', 'Non-surgical treatments to revive your skin tone.', 'Our medical aesthetics team designs individualized skin rejuvenation plans for radiant results.', 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80'),
('Smile Design', 'smile-design', 'Dental Treatments', 'Comprehensive aesthetic dentistry and restorative care.', 'Smile design combines digital planning with expert dentistry for a confident smile.', 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=1200&q=80');
