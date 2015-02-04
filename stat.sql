SELECT p.*, SUM(stat.balance) balance, SUM(stat.orders) orders
FROM products p
JOIN (
    SELECT products_id product_id, balance, 0 orders
    FROM werehouse
    WHERE TO_DAYS(NOW()) - TO_DAYS(date) <= 1
    UNION ALL
    SELECT product_id, 0 balance, orders
    FROM agent_stats
    WHERE TO_DAYS(NOW()) - TO_DAYS(date) <= 1
) stat ON (p.id = stat.product_id)
GROUP BY p.id;