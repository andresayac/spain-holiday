<?php


use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Calendarioslaboral
{

    private $URL_BASE = 'https://www.calendarioslaborales.com/';
    private $headers = ['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36 '];

    public function __construct()
    {
        $this->client_calendario_laboral = new Client(
            [
                'base_uri' => $this->URL_BASE,
                'cookies' => true,
                'timeout' => 10,
                'verify' => false
            ]
        );
    }

    public function getProvinces()
    {
        $response =  $this->client_calendario_laboral->request('GET', '/', [
            'headers' => $this->headers
        ]);

        if ($response->getStatusCode() != 200) return [];

        $contents = $response->getBody()->getContents();

        $crawler = new Crawler($contents);
        $data = $crawler->filter('#formBuscador > form > div:nth-child(1) > select > option');
        $data_provincias = [];
        $data_provincias = $data->each(function ($node) {
            $tmp_array = [];
            $tmp_array['slug'] =  $node->attr('value');
            $tmp_array['name'] = $node->text();
            return  $tmp_array;
        });

        return $data_provincias ?? [];
    }

    public function getYearsAvailable()
    {
        $response =  $this->client_calendario_laboral->request('GET', '/', [
            'headers' => $this->headers
        ]);

        if ($response->getStatusCode() != 200) return [];

        $contents = $response->getBody()->getContents();

        $crawler = new Crawler($contents);
        $data = $crawler->filter('#formBuscador > form > div:nth-child(2) > select > optgroup');
        $data_year_available = [];
        $data_year_available = $data->each(function ($node) {
            $tmp_array = [];
            $tmp_array = $node->text();
            return  $tmp_array;
        });

        $data_year_available = [
            'current' => explode(' ', $data_year_available[0]),
            'next' => explode(' ', $data_year_available[1]),
            'previous' => explode(' ', $data_year_available[2])
        ];

        return $data_year_available ?? [];
    }


    public function getHolidays(string $province_slug = NULL, $year = NULL)
    {
        $year =  $year ?? date("Y");
        $GLOBALS['year'] =  $year;

        $response =  $this->client_calendario_laboral->request('GET', "/calendario-laboral-{$province_slug}-{$year}.htm", [
            'headers' => $this->headers
        ]);

        if ($response->getStatusCode() != 200) return [];

        $contents = $response->getBody()->getContents();
        $crawler = new Crawler($contents);

        try {
            $crawler->filter('title')->text();
        } catch (\Exception $e) {
            return 'the requested province does not exist';
        }

        $data = $crawler->filter('#wrapIntoMeses > div > div > ul > li');

        $data_year_available = $data->each(function ($node) {
            $tmp_array = [];

            $types_holiday = [
                'festivoP' => 'local',
                'festivoN' => 'national',
                'festivoR' => 'regional'
            ];

            $tmp_array_text = explode('.', $node->text());

            $tmp_array['type'] = $types_holiday[$node->filter('span')->attr('class')];
            $tmp_array['day'] = $this->changeDayFormat($tmp_array_text[0], $GLOBALS['year']);
            $tmp_array['holiday'] = $tmp_array_text[1];

            return  $tmp_array;
        });

        return $data_year_available;
    }

    public function isHoliday($date = NULL, array $holidays = [])
    {
        $date = $date ?? date("Y-m-d");
        $input_date_check = new DateTime($date);

        foreach ($holidays as $holiday) {
            $tmp_date_check = new DateTime($holiday['day']);
            if ($tmp_date_check == $input_date_check) return true;
        }

        return false;
    }

    private function changeDayFormat($text, $year)
    {
        $month_array = $this->month();
        $array_text = explode(' ', $text);
        $day = str_pad($array_text[0], 2, '0', STR_PAD_LEFT);
        $month = $month_array[strtolower($array_text[2])];

        return ("{$year}-{$month}-{$day}");
    }

    private function month()
    {
        return [
            'enero' => '01',
            'febrero' => '02',
            'marzo' => '03',
            'abril' => '04',
            'mayo' => '05',
            'junio' => '06',
            'julio' => '07',
            'agosto' => '08',
            'septiembre' => '09',
            'octubre' => '10',
            'noviembre' => '11',
            'diciembre' => '12',
        ];
    }
}
