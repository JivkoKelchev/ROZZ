UPDATE lands l1
    JOIN (
    SELECT
    l2.zem_id,
    l2.mest_id,
    l2.kat_id,
    l2.num
    FROM
    lands l2
    WHERE
    l2.num NOT LIKE '%.%'
    ) AS l2_clean
ON l1.zem_id = l2_clean.zem_id
    AND l1.mest_id = l2_clean.mest_id
    AND
    concat(LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(l1.num, '.', -2), '.', 1), 3, '0'), LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(l1.num, '.', -2), '.', -1), 3, '0'))
    = l2_clean.num
    SET l1.kat_id = l2_clean.kat_id
WHERE l1.num LIKE '%.%';