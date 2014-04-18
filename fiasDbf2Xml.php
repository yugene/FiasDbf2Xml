<?php

/**
 * Class for FIAS DBF files/deltas to XML files/deltas conversion
 *
 * @version 1.0
 * @link http://fias.nalog.ru/Public/DownloadPage.aspx
 * @link http://basicdata.ru/
 */
class FiasDbf2Xml
{
    protected $_files;
    protected $_fileCache = array();

    protected $_tables = array(
        'ACTSTAT' => array(
            'container' => 'ActualStatuses',
            'item' => 'ActualStatus'
        ),
        'ADDROBJ' => array(
            'container' => 'AddressObjects',
            'item' => 'Object'
        ),
        'CENTERST' => array(
            'container' => 'CenterStatuses',
            'item' => 'CenterStatus'
        ),
        'CURENTST' => array(
            'container' => 'CurrentStatuses',
            'item' => 'CurrentStatus'
        ),
        'ESTSTAT' => array(
            'container' => 'EstateStatuses',
            'item' => 'EstateStatus'
        ),
        'HOUSEINT' => array(
            'container' => 'HouseIntervals',
            'item' => 'HouseInterval'
        ),
        'HOUSE' => array(
            'container' => 'Houses',
            'item' => 'House'
        ),
        'HSTSTAT' => array(
            'container' => 'HouseStateStatuses',
            'item' => 'HouseStateStatus'
        ),
        'INTVSTAT' => array(
            'container' => 'IntervalStatuses',
            'item' => 'IntervalStatus'
        ),
        'LANDMARK' => array(
            'container' => 'Landmarks',
            'item' => 'Landmark'
        ),
        'NDOCTYPE' => array(
            'container' => 'NormativeDocumentTypes',
            'item' => 'NormativeDocumentType'
        ),
        'NORMDOC' => array(
            'container' => 'NormativeDocumentes',
            'item' => 'NormativeDocument'
        ),
        'OPERSTAT' => array(
            'container' => 'OperationStatuses',
            'item' => 'OperationStatus'
        ),
        'SOCRBASE' => array(
            'container' => 'AddressObjectTypes',
            'item' => 'AddressObjectType'
        ),
        'STRSTAT' => array(
            'container' => 'StructureStatuses',
            'item' => 'StructureStatus'
        ),
    );

    /**
     * Print a message
     *
     * @param string $message
     */
    protected function _println($message)
    {
        echo $message . PHP_EOL;
    }

    /**
     * Add a slash to the end of a string
     *
     * @param string $text
     * @return string
     */
    protected function _finalSlash($text)
    {
        if (substr($text, -1) != '/')
        {
            $text = $text . '/';
        }

        return $text;
    }

    /**
     * Check command line options, files, etc
     *
     * @return bool
     */
    protected function _init()
    {
        global $argv, $argc;

        if ($argc != 2)
        {
            $this->_printUsage();
            return false;
        }

        $source = trim($argv[1]);

        if (strlen($source) == 0)
        {
            $this->_printUsage();
            return false;
        }

        if (! file_exists($source))
        {
            $this->_println("File {$source} does not exist");
            $this->_println('');
            $this->_printUsage();
            return false;
        }

        $files = array();

        if (is_dir($source))
        {
            $files = scandir($source);

            foreach ($files as $i => $file)
            {
                if (strtoupper(substr($file, -4)) == '.DBF')
                {
                    $files[$i] = $this->_finalSlash($source) . $file;
                }
            }
        }
        else if (is_file($source))
        {
            if (is_readable($source) && strtoupper(substr($source, -4)) == '.DBF')
            {
                $files[] = $source;
            }
            else
            {
                $this->_println("Cannot read file {$source}");
                $this->_println('');
                $this->_printUsage();
                return false;
            }
        }
        else
        {
            $this->_println("File {$source} does not exist");
            $this->_println('');
            $this->_printUsage();
            return false;
        }

        if (! $this->_checkSetFiles($files))
        {
            $this->_println("No known tables found for {$source}");
            return false;
        }

        return true;
    }

    /**
     * Check whether files contain known tables
     *
     * @param array $files
     * @return bool
     */
    protected function _checkSetFiles($files)
    {
        $this->_files = array();

        foreach ($files as $file)
        {
            $table = $this->_getTableFromFile($file);

            if (! is_null($table))
            {
                $this->_files[$file] = $table;
            }
        }

        return (count($this->_files) > 0);
    }

    /**
     * Print usage info
     */
    protected function _printUsage()
    {
        $this->_println('Usage: fiasDbf2Xml.php <path>');
        $this->_println('  Converts FIAS DBF files to XML.');
        $this->_println('');
    }

    /**
     * Get table name from valid file name or null
     *
     * @param string $fileName
     * @return string
     */
    protected function _getTableFromFile($fileName)
    {
        $tableName = str_replace(
            array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.DBF'),
            '',
            strtoupper(basename($fileName))
        );

        if (! isset($this->_tables[$tableName]))
        {
            $tableName = null;
        }

        return $tableName;
    }

    /**
     * Get XML file name for a table
     *
     * @param string $table
     * @return string
     */
    protected function _getXmlFileName($table)
    {
        return 'AS_' . $table . '.XML';
    }

    /**
     * Write data to XML file
     *
     * @param string $table
     * @param string $data
     */
    protected function _write($table, $data)
    {
        $xmlFile = $this->_getXmlFileName($table);

        if (! isset($this->_fileCache[$xmlFile]))
        {
            $this->_fileCache[$xmlFile] = '</' . $this->_tables[$table]['container'] . '>';

            // UTF-8 BOM and XML header
            file_put_contents(
                $xmlFile,
                chr(0xEF) . chr(0xBB) . chr(0xBF) . '<?xml version="1.0" encoding="utf-8"?><' . $this->_tables[$table]['container'] . '>'
            );
        }

        file_put_contents(
            $xmlFile,
            $data,
            FILE_APPEND
        );
    }

    /**
     * Write closing tags to XML files
     */
    protected function _finalizeFiles()
    {
        foreach ($this->_fileCache as $xmlFile => $data)
        {
            file_put_contents(
                $xmlFile,
                $data,
                FILE_APPEND
            );
        }
    }

    /**
     * Convert DBF to XML encoding
     *
     * @param string $value
     * @return string
     */
    protected function _convertEncoding($value)
    {
        return iconv('cp866', 'utf8', $value);
    }

    /**
     * Iterate through files and lines
     */
    public function run()
    {
        if ($this->_init())
        {
            foreach ($this->_files as $file => $table)
            {
                $db = dbase_open($file, 0);

                if ($db)
                {
                    $recordNumber = dbase_numrecords($db);
                    $this->_println($file . ': ' . $recordNumber . ' records');

                    for ($i = 1; $i <= $recordNumber; $i ++)
                    {
                        $item = array(
                            '<' . $this->_tables[$table]['item']
                        );

                        $row = dbase_get_record_with_names($db, $i);
                        foreach ($row as $key => $value)
                        {
                            $key = strtoupper($key);
                            $value = $this->_convertEncoding(trim($value));

                            if (strlen($value) > 0 && $key != 'DELETED')
                            {
                                $item[] = $key . '="' . $value . '"';
                            }
                        }

                        $item[] = "/>";

                        $this->_write($table, join(' ', $item));
                    }

                    dbase_close($db);
                }
                else
                {
                    $this->_println('DBF open failed: ' . $file);
                }
            }

            $this->_finalizeFiles();
        }
    }
}

$fiasDbf2Xml = new FiasDbf2Xml();
$fiasDbf2Xml->run();
