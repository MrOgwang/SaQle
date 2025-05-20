<?php
namespace SaQle\Orm\Database\Manager\Base;

abstract class DbManager{
	 public array $charset_and_collations = [
	     'big5'     => ['description' => 'Big5 Traditional Chinese',    'collations' => ['big5_chinese_ci']],
		 'dec8'     => ['description' => 'DEC West European',           'collations' => ['dec8_swedish_ci']],
		 'cp850'    => ['description' => 'DOS West European',           'collations' => ['cp850_general_ci']],
		 'hp8'      => ['description' => 'HP West European',            'collations' => ['hp8_english_ci']],
		 'koi8r'    => ['description' => 'KOI8-R Relcom Russian',       'collations' => ['koi8r_general_ci']],
		 'latin1'   => ['description' => 'cp1252 West European',        'collations' => ['latin1_swedish_ci']],
		 'latin2'   => ['description' => 'ISO 8859-2 Central European', 'collations' => ['latin2_general_ci']],
		 'swe7'     => ['description' => '7bit Swedish',                'collations' => ['swe7_swedish_ci']],
		 'ascii'    => ['description' => 'US ASCII',                    'collations' => ['ascii_general_ci']],
		 'ujis'     => ['description' => 'EUC-JP Japanese',             'collations' => ['ujis_japanese_ci']],
		 'sjis'     => ['description' => 'Shift-JIS Japanese',          'collations' => ['sjis_japanese_ci']],
		 'hebrew'   => ['description' => 'ISO 8859-8 Hebrew',           'collations' => ['hebrew_general_ci']],
		 'tis620'   => ['description' => 'TIS620 Thai',                 'collations' => ['tis620_thai_ci']],
		 'euckr'    => ['description' => 'EUC-KR Korean',               'collations' => ['euckr_korean_ci']],
		 'koi8u'    => ['description' => 'KOI8-U Ukrainian',            'collations' => ['koi8u_general_ci']],
		 'gb2312'   => ['description' => 'GB2312 Simplified Chinese',   'collations' => ['gb2312_chinese_ci']],
		 'greek'    => ['description' => 'ISO 8859-7 Greek',            'collations' => ['greek_general_ci']],
		 'cp1250'   => ['description' => 'Windows Central European',    'collations' => ['cp1250_general_ci']],
		 'gbk'      => ['description' => 'GBK Simplified Chinese',      'collations' => ['gbk_chinese_ci']],
		 'latin5'   => ['description' => 'ISO 8859-9 Turkish',          'collations' => ['latin5_turkish_ci']],
		 'armscii8' => ['description' => 'ARMSCII-8 Armenian',          'collations' => ['armscii8_general_ci']],
		 'utf8'     => ['description' => 'UTF-8 Unicode',               'collations' => ['utf8_general_ci']],
		 'ucs2'     => ['description' => 'UCS-2 Unicode',               'collations' => ['ucs2_general_ci']],
		 'cp866'    => ['description' => 'DOS Russian',                 'collations' => ['cp866_general_ci']],
		 'keybcs2'  => ['description' => 'DOS Kamenicky Czech-Slovak',  'collations' => ['keybcs2_general_ci']],
		 'macce'    => ['description' => 'Mac Central European',        'collations' => ['macce_general_ci']],
		 'macroman' => ['description' => 'Mac West European',           'collations' => ['macroman_general_ci']],
		 'cp852'    => ['description' => 'DOS Central European',        'collations' => ['cp852_general_ci']],
		 'latin7'   => ['description' => 'ISO 8859-13 Baltic',          'collations' => ['latin7_general_ci']],
		 'utf8mb4'  => ['description' => 'UTF-8 Unicode',               'collations' => ['utf8mb4_general_ci']],
		 'cp1251'   => ['description' => 'Windows Cyrillic',            'collations' => ['cp1251_general_ci']],
		 'utf16'    => ['description' => 'UTF-16 Unicode',              'collations' => ['utf16_general_ci']],
		 'utf16le'  => ['description' => 'UTF-16LE Unicode',            'collations' => ['utf16le_general_ci']],
		 'cp1256'   => ['description' => 'Windows Arabic',              'collations' => ['cp1256_general_ci']],
		 'cp1257'   => ['description' => 'Windows Baltic',              'collations' => ['cp1257_general_ci']],
		 'utf32'    => ['description' => 'UTF-32 Unicode',              'collations' => ['utf32_general_ci']],
		 'binary'   => ['description' => 'Binary pseudo charset',       'collations' => ['binary']],
		 'geostd8'  => ['description' => 'GEOSTD8 Georgian',            'collations' => ['geostd8_general_ci']],
		 'cp932'    => ['description' => 'SJIS for Windows Japanese',   'collations' => ['cp932_japanese_ci']],
		 'eucjpms'  => ['description' => 'UJIS for Windows Japanese',   'collations' => ['eucjpms_japanese_ci']],
	 ];
	 protected $connection = null;
	 protected $vconnection = null;
	 protected $connection_params = null;
	 abstract public function create_database();
}

