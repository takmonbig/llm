-- Long Live Men — initial seed (no fake orders, sales or reviews)
SET NAMES utf8mb4;

INSERT IGNORE INTO products
(id, slug, title, subtitle, short_description, description, price, compare_at_price, sku, stock, is_combo, is_active, is_featured, bottle_count, tablet_count,
 ingredients, usage_directions, warnings, storage_instructions, manufacturer_info, verification_notes, seo_title, seo_description)
VALUES
(1, 'advanced-focus-t-boost-2x', 'Advanced Focus T Boost – 2X', 'Thai Boost + African Power · Two-bottle combo',
 'A two-bottle Ayurvedic wellness combo from Long Live Men: Thai Boost Tablets (60) and African Power Tablets (60) — 120 tablets in total.',
 'Advanced Focus T Boost – 2X brings together both Long Live Men Ayurvedic proprietary tablets in one pack. The green-label Thai Boost bottle and the black-and-gold African Power bottle each contain 60 tablets, giving you 120 tablets in total.\n\nBoth products are labelled as Ayurvedic proprietary medicines and are marketed by Ayota Enterprises. Please read the full ingredient list, label directions and warnings below before use, and consult a qualified healthcare professional if you have any medical condition, take medication, or are unsure whether this product is suitable for you.',
 2999.00, NULL, 'LLM-AFTB-2X', 100, 1, 1, 1, 2, 120,
 'Each tablet (both bottles, as printed on the label) — Herbal extracts:\nShatavari (Asparagus racemosus) Root — 120 mg\nAshwagandha (Withania somnifera) Root — 120 mg\nSafed Musli (Chlorophytum borivilianum) Root — 100 mg\nChhota Gokhru (Tribulus terrestris) Fruit — 100 mg\nTalmakhana (Hygrophila spinosa) Seed — 90 mg\nKuchla (Strychnos nux-vomica) Seed — 90 mg\nKaunch (Mucuna pruriens) Seed — 80 mg\nAkarkara (Anacyclus pyrethrum) Root — 50 mg\nPine Bark (Pinus pinaster) — 50 mg\nShilajeet (Asphaltum punjabianum) Resin — 40 mg\nJaiphal (Myristica fragrans) Fruit — 20 mg\nLaung (Syzygium aromaticum) Fruit — [verify quantity]\nKeshar (Crocus sativus) — [verify quantity]',
 'As printed on the label — Suggested Adult Use: Take 1–2 tablets twice a day with lukewarm water after meals, or as directed by a healthcare professional.\n\nThis is the manufacturer''s label text reproduced for information only. Do not self-medicate; follow the advice of a qualified practitioner.',
 'Not a substitute for professional medical advice. Consult a qualified healthcare professional before use, especially if you have a medical condition, take any medication, or are under 18.\nThe label lists Kuchla (Strychnos nux-vomica), an ingredient that requires care — follow professional guidance and never exceed the label direction.\nKeep out of reach of children. Discontinue use and seek medical advice if you experience any adverse reaction.\nIndividual results vary. No therapeutic claims are made beyond the manufacturer''s label.',
 'Store in a cool, dark and dry place (as printed on the label). Keep the bottle tightly closed.',
 'An Ayurvedic Proprietary Medicine.\nMarketed by: Ayota Enterprises, Flat No. 201, Tower 3, TDI Espania, Opposite Jurasik Park, Sonipat, Haryana 131001. Website: www.longlivemen.com\nManufactured by: Space Organics, HSIDC Industrial Estate, Rai, Sonipat – 131029 (HR). Mfg. Lic. No.: 665-ISM(HR).\nCertification marks printed on label: GMP, ISO 22000, HACCP — [verify current certificates before advertising them].',
 'Transcribed from supplied photographs (Oct 2026). VERIFY BEFORE PUBLISHING: (1) exact mg of Laung and Keshar were not legible; (2) African Power label lists Akarkara 50 mg while Thai Boost photo appears to list 80 mg — confirm which is correct per bottle; (3) manufacturer address lines are partially blurred; (4) label MRP values (₹1,400 / ₹1,600 per bottle) are not shown on the site because the lawful combo MRP is unconfirmed; (5) certification badges need current certificates; (6) Kuchla (nux-vomica) content requires regulatory/safety review before any dosage text is published.',
 'Advanced Focus T Boost – 2X | Thai Boost + African Power Combo | Long Live Men',
 'Buy the Long Live Men two-bottle Ayurvedic wellness combo — Thai Boost (60 tablets) + African Power (60 tablets) for ₹2,999 with free shipping across India.');

INSERT IGNORE INTO product_variants (id, product_id, name, tablet_count, description, image_path, is_sellable, sort_order) VALUES
(1, 1, 'Thai Boost Tablets', 60, 'Green-label bottle. Labelled as an Ayurvedic proprietary medicine with herbal extracts including Shatavari, Ashwagandha, Safed Musli and Gokhru. 60 tablets per bottle.', 'assets/images/products/thai-boost-front.jpg', 0, 1),
(2, 1, 'African Power Tablets', 60, 'Black-and-gold label bottle. Labelled as an Ayurvedic proprietary medicine with herbal extracts including Shatavari, Ashwagandha, Kaunch and Shilajeet. 60 tablets per bottle.', 'assets/images/products/african-power-front.jpg', 0, 2);

INSERT IGNORE INTO product_images (id, product_id, path, media_type, poster_path, alt_text, sort_order) VALUES
(1, 1, 'assets/images/gallery/combo-both-bottles.jpg', 'image', NULL, 'Advanced Focus T Boost – 2X combo: Thai Boost and African Power bottles side by side', 0),
(2, 1, 'assets/images/gallery/the-2x-combo.jpg', 'image', NULL, 'The 2X Combo — Thai Boost 60 tablets plus African Power 60 tablets, both bottles one combo, ₹2,999', 1),
(3, 1, 'assets/video/combo-video-1.mp4', 'video', 'assets/images/gallery/combo-both-bottles.jpg', 'Long Live Men combo product video', 2),
(4, 1, 'assets/images/gallery/discover-whats-inside.jpg', 'image', NULL, 'Discover what''s inside — Ashwagandha, Shatavari, Safed Musli and Gokhru botanicals', 3),
(5, 1, 'assets/images/gallery/combo-back-labels.jpg', 'image', NULL, 'Back labels of Thai Boost and African Power bottles showing ingredients', 4),
(6, 1, 'assets/images/gallery/made-for-your-everyday.jpg', 'image', NULL, 'Made for your everyday — Long Live Men wellness combo lifestyle image', 5),
(7, 1, 'assets/images/gallery/global-inspiration.jpg', 'image', NULL, 'Global inspiration, everyday wellness — Long Live Men combo', 6),
(8, 1, 'assets/video/combo-video-2.mp4', 'video', 'assets/images/gallery/the-2x-combo.jpg', 'Long Live Men combo product video 2', 7),
(9, 1, 'assets/images/gallery/delivered-free.jpg', 'image', NULL, 'Your combo delivered free — free shipping across India', 8),
(10, 1, 'assets/images/gallery/make-wellness-part-of-your-day.jpg', 'image', NULL, 'Make wellness part of your day — Long Live Men', 9),
(11, 1, 'assets/images/gallery/trustified-badge.jpg', 'image', NULL, 'Trustified badge graphic supplied by brand — verify certification before relying on it', 10),
(12, 1, 'assets/images/products/thai-boost-ingredients.jpg', 'image', NULL, 'Thai Boost back label photo showing suggested use and ingredient list', 11),
(13, 1, 'assets/images/products/african-power-ingredients.jpg', 'image', NULL, 'African Power back label photo showing suggested use and ingredient list', 12),
(14, 1, 'assets/images/products/thai-boost-info.jpg', 'image', NULL, 'Thai Boost side label with manufacturer, licence and batch details', 13),
(15, 1, 'assets/images/products/african-power-info.jpg', 'image', NULL, 'African Power side label with manufacturer, licence and batch details', 14);

INSERT IGNORE INTO store_settings (setting_key, setting_value) VALUES
('store_name', 'Long Live Men'),
('store_tagline', 'Premium Men''s Wellness'),
('support_email', ''),
('support_phone', ''),
('business_address', ''),
('announcement_text', 'FREE SHIPPING ACROSS INDIA'),
('announcement_extra', 'Discover the Advanced Focus T Boost – 2X Combo|Premium Men''s Wellness'),
('cod_enabled', '1'),
('razorpay_enabled', '1'),
('free_shipping', '1'),
('flat_shipping_fee', '0'),
('seo_title', 'Long Live Men — Premium Men''s Ayurvedic Wellness'),
('seo_description', 'Long Live Men: the Advanced Focus T Boost – 2X two-bottle Ayurvedic wellness combo. Free shipping across India.'),
('store_logo', 'assets/images/brand/logo-src.png'),
('social_instagram', ''),
('social_facebook', ''),
('social_youtube', ''),
('medical_disclaimer', 'The information on this website, including product descriptions and label text, is provided for general information only and is not a substitute for advice from a qualified healthcare professional. Products are not intended to diagnose, treat, cure or prevent any disease. Always read the label and consult a practitioner before use.'),
('about_text', '[Administrator: replace this placeholder with your verified brand story.] Long Live Men is a men''s wellness brand offering the Advanced Focus T Boost – 2X two-bottle Ayurvedic combo. We believe everyday wellbeing starts with informed choices — clear labels, honest information and products you can read about before you buy.'),
('shipping_policy', '[Administrator: verify and complete.] We currently offer free shipping across India, subject to serviceability of your PIN code. Orders are dispatched after confirmation; tracking details are added to your order once it ships. Delivery timelines depend on your location and the courier partner.'),
('return_policy', '[Administrator: replace with your actual return and refund terms.] Please contact support with your order number if you receive a damaged, incorrect or incomplete order. Due to the nature of consumable wellness products, opened bottles may not be eligible for return. Refunds, where approved, are processed to the original payment method.'),
('privacy_policy', '[Administrator: verify and complete.] We collect only the information needed to process your order and provide support: name, contact details, delivery address and order history. Payments are processed by Razorpay; we never store card details. We do not sell your personal information.'),
('terms_text', '[Administrator: verify and complete.] By using this website you agree to these terms. Prices are listed in INR and include applicable taxes unless stated otherwise. Orders are subject to acceptance and availability. These terms are governed by the laws of India.'),
('disclaimer_text', '[Administrator: verify.] Products sold on this website are Ayurvedic proprietary medicines as labelled by the manufacturer. Statements on this website have not been evaluated for therapeutic claims. This product is not intended to diagnose, treat, cure or prevent any disease. Consult a qualified healthcare professional before use.'),
('hero_banners', '[{"image":"assets/images/banners/banner-6.jpg","alt":"2X Wellness Combo — just ₹2,999. Thai Boost and African Power bottles. Shop now.","link":"product/advanced-focus-t-boost-2x"},{"image":"assets/images/banners/banner-1.jpg","alt":"Meet Long Live Men — discover our wellness combo","link":"product/advanced-focus-t-boost-2x"},{"image":"assets/images/banners/banner-3.jpg","alt":"Own your day. Everyday wellness, your way.","link":"product/advanced-focus-t-boost-2x"},{"image":"assets/images/banners/banner-2.jpg","alt":"Free shipping across India","link":"shipping-policy"},{"image":"assets/images/banners/banner-4.jpg","alt":"Explore the botanicals — discover what''s inside","link":"product/advanced-focus-t-boost-2x#details"},{"image":"assets/images/banners/banner-5.jpg","alt":"Shop with ease — secure checkout, COD available","link":"product/advanced-focus-t-boost-2x"}]'),
('faq_json', '[{"q":"What is included in the combo?","a":"The Advanced Focus T Boost – 2X combo includes two bottles: Thai Boost Tablets (60 tablets) and African Power Tablets (60 tablets)."},{"q":"How many tablets are included?","a":"120 tablets in total — 60 in each bottle."},{"q":"What is the combo price?","a":"₹2,999 for the two-bottle combo."},{"q":"Is shipping free?","a":"Yes. Shipping is free across India, subject to serviceability of your PIN code."},{"q":"How can I track my order?","a":"Use the Track Order page with your order number and the email used at checkout. We will send you a secure tracking link. Registered customers can also see tracking in their account."},{"q":"Which payment methods are available?","a":"Online payment via Razorpay (UPI, cards, net banking and wallets as enabled) and Cash on Delivery where available."},{"q":"Where can I find ingredient information?","a":"The full label ingredient list is shown on the product page under Ingredients, together with label directions, storage instructions and warnings."},{"q":"How can I contact customer support?","a":"Use the Contact page to send us a message. Support contact details are shown in the footer once configured by the store."}]');
