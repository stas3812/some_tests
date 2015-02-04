<?php

class Categories
{
    /** Correct credentials are required */
    const DB_HOST     = '';
    const DB_USER     = '';
    const DB_PASS     = '';
    const DB_DATABASE = '';

    protected $_db;

    /**
     * Static alias for getCategories($sector, $industry)
     * @param string $sector_name
     * @param string $industry_name
     * @return array
     */
    public static function get_category($sector_name, $industry_name)
    {
        $obj = new self();
        return $obj->getCategories($sector_name, $industry_name);
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $db = mysqli_connect(self::DB_HOST, self::DB_USER, self::DB_PASS, self::DB_DATABASE);
        if (!$db)
            throw new Exception('DB connection error: ' . mysqli_connect_error());

        $result = mysqli_query($db, 'SET NAMES UTF8');
        if(!$result)
            throw new Exception('Can`t set UTF-8 as default charset for connection');

        $this->_db = $db;
    }

    /**
     * @param string $sector_name
     * @param string $industry_name
     * @return array
     * @throws Exception
     */
    public function getCategories($sector, $industry)
    {
        if(!$sector)
            throw new Exception('Sector name is required');

        if(!$industry)
            throw new Exception('Industry name is required');

        $result = mysqli_query($this->_db, 'SELECT c1.id sector_id, c2.id industry_id
            FROM categories c1
            LEFT JOIN categories c2 ON (c1.id = c2.parent_id AND c2.name = "' . $this->_escapeValue($industry) . '")
            WHERE c1.name = "' . $this->_escapeValue($sector) . '"');

        if(!$result)
            throw new Exception(mysqli_error($this->_db));

        list($sectorId, $industryId) = mysqli_fetch_array($result);

        if(!$sectorId)
            $sectorId = $this->createCategory($sector);

        if(!$industryId)
            $industryId = $this->createCategory($industry, $sectorId);

        return array($sectorId, $industryId);
    }

    /**
     * @param string $name
     * @param int $parentId
     * @return int
     * @throws Exception
     */
    public function createCategory($name, $parentId = 0)
    {
        $result = mysqli_query(
            $this->_db,
            'INSERT INTO categories(parent_id, name) VALUES(' . (int)$parentId . ', "' . $this->_escapeValue($name) . '")'
        );

        if(!$result)
            throw new Exception(mysqli_error($this->_db));

        return mysqli_insert_id($this->_db);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function _escapeValue($value)
    {
        return mysqli_real_escape_string($this->_db, $value);
    }
}

/**
list($sector_id, $industry_id) = Categories::get_category('сектор1', 'индустрия1');
echo "Sector ID: $sector_id\nIndustry ID:$industry_id\n";
*/
