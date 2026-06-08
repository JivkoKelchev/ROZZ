-- =====================================================================
-- РОЗЗ — комбинирана миграция за издание v0.2.0 (2026-06-08)
-- =====================================================================
-- Само ДОБАВЯЩИ операции: нови колони / нови таблици / nullable външни ключове.
-- Нищо съществуващо не се изтрива и не се променя типът му.
-- Единствената промяна по съществуващи редове: всички договори стават BGN
-- (предевровите договори остават в лева).
--
-- ВАЖНО: изпълнете ВЕДНЪЖ. Преди това проверете, че колоните/таблиците ги няма
-- (виж DELIVERY.md, стъпка 3). Наследеният шаблон НЕ се създава тук — той
-- съдържа кирилица и се създава през приложението (виж DELIVERY.md, стъпка 5).
-- =====================================================================


-- ---------------------------------------------------------------------
-- 1) Валута на договора (BGN / EUR) — преход към еврото.
--    Съществуващите договори се маркират като BGN; празно/ново = EUR.
-- ---------------------------------------------------------------------
ALTER TABLE contracts     ADD currency VARCHAR(3) DEFAULT NULL;
ALTER TABLE new_contracts ADD currency VARCHAR(3) DEFAULT NULL;

UPDATE contracts SET currency = 'BGN' WHERE currency IS NULL;
-- new_contracts са временни чернови; NULL (= EUR) е напълно нормално.


-- ---------------------------------------------------------------------
-- 2) Редактируеми, версионирани шаблони за договори.
--    Таблицата се създава ПРЕДИ външния ключ от contracts.
--    Старите договори остават с template_id = NULL и ползват наследения шаблон.
-- ---------------------------------------------------------------------
CREATE TABLE contract_template (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL,
    row_template LONGTEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

ALTER TABLE contracts ADD template_id INT DEFAULT NULL;
ALTER TABLE contracts ADD CONSTRAINT FK_2576E0FD5DA0FB8 FOREIGN KEY (template_id) REFERENCES contract_template (id);
CREATE INDEX IDX_2576E0FD5DA0FB8 ON contracts (template_id);


-- ---------------------------------------------------------------------
-- 3) Стандартни цени по НТП и землище (попълват се автоматично в договора).
-- ---------------------------------------------------------------------
CREATE TABLE ntp_zem_price (
    id INT AUTO_INCREMENT NOT NULL,
    ntp_id INT NOT NULL,
    zem_id INT NOT NULL,
    price DOUBLE PRECISION NOT NULL,
    UNIQUE INDEX UNIQ_ntp_zem (ntp_id, zem_id),
    INDEX IDX_ntp_zem_price_ntp (ntp_id),
    INDEX IDX_ntp_zem_price_zem (zem_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

ALTER TABLE ntp_zem_price ADD CONSTRAINT FK_ntp_zem_price_ntp FOREIGN KEY (ntp_id) REFERENCES ntp (id);
ALTER TABLE ntp_zem_price ADD CONSTRAINT FK_ntp_zem_price_zem FOREIGN KEY (zem_id) REFERENCES zem (id);

-- =====================================================================
-- Край на миграцията. След това: изчистете var\cache\prod и създайте
-- наследения шаблон (DELIVERY.md, стъпки 4–5).
-- =====================================================================
