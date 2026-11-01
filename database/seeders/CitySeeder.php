<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [
            [
                'province'    => 'اردبيل',
                'city'        => 'اردبيل',
                'latitude'    => '38.24974442',
                'longitude'   => '48.29428482'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'اصلاندوز',
                'latitude'    => '39.44260406',
                'longitude'   => '47.41002274'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'آبي بيگلو',
                'latitude'    => '38.28489685',
                'longitude'   => '48.55250549'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'بيله سوار',
                'latitude'    => '39.37607956',
                'longitude'   => '48.34270859'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'پارس آباد',
                'latitude'    => '39.64579391',
                'longitude'   => '47.90995026'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'تازه كند',
                'latitude'    => '39.57558823',
                'longitude'   => '48.0163765'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'تازه كندانگوت',
                'latitude'    => '39.04096603',
                'longitude'   => '47.74365234'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'جعفرآباد',
                'latitude'    => '39.43317795',
                'longitude'   => '48.09788132'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'خلخال',
                'latitude'    => '37.62075043',
                'longitude'   => '48.52736664'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'رضي',
                'latitude'    => '38.62902069',
                'longitude'   => '48.09450912'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'سرعين',
                'latitude'    => '38.13766861',
                'longitude'   => '48.07623291'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'عنبران',
                'latitude'    => '38.48768997',
                'longitude'   => '48.43514252'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'فخرآباد',
                'latitude'    => '38.52298737',
                'longitude'   => '47.86167145'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'كلور',
                'latitude'    => '37.38945389',
                'longitude'   => '48.72290421'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'كوراييم',
                'latitude'    => '37.95056915',
                'longitude'   => '48.23551559'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'گرمي',
                'latitude'    => '39.01218796',
                'longitude'   => '48.08360291'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'گيوي',
                'latitude'    => '37.68416214',
                'longitude'   => '48.33966827'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'لاهرود',
                'latitude'    => '38.50733566',
                'longitude'   => '47.83047867'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'مرادلو',
                'latitude'    => '38.74676132',
                'longitude'   => '47.74628448'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'مشگين شهر',
                'latitude'    => '38.39718628',
                'longitude'   => '47.67258072'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'نمين',
                'latitude'    => '38.42364883',
                'longitude'   => '48.4808197'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'نير',
                'latitude'    => '38.03521729',
                'longitude'   => '48.00739288'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'هشتجين',
                'latitude'    => '37.36382675',
                'longitude'   => '48.32458496'
            ],
            [
                'province'    => 'اردبيل',
                'city'        => 'هير',
                'latitude'    => '38.07535553',
                'longitude'   => '48.4979744'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ابريشم',
                'latitude'    => '32.5561142',
                'longitude'   => '51.57363129'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ابوزيدآباد',
                'latitude'    => '33.90413284',
                'longitude'   => '51.76800156'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'اردستان',
                'latitude'    => '33.35657883',
                'longitude'   => '52.37818527'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'اژيه',
                'latitude'    => '32.44017029',
                'longitude'   => '52.3795166'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'اصفهان',
                'latitude'    => '32.57763672',
                'longitude'   => '51.6590538'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'افوس',
                'latitude'    => '33.0241394',
                'longitude'   => '50.09297943'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'انارك',
                'latitude'    => '33.30680084',
                'longitude'   => '53.69702911'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ايمانشهر',
                'latitude'    => '32.47034836',
                'longitude'   => '51.46138382'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'آران وبيدگل',
                'latitude'    => '34.05819702',
                'longitude'   => '51.48248672'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'بادرود',
                'latitude'    => '33.68975067',
                'longitude'   => '52.0112648'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'باغ بهادران',
                'latitude'    => '32.37582016',
                'longitude'   => '51.19124222'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'بافران',
                'latitude'    => '32.83668137',
                'longitude'   => '53.14214325'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'برزك',
                'latitude'    => '33.7783165',
                'longitude'   => '51.23015213'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'برف انبار',
                'latitude'    => '32.9958725',
                'longitude'   => '50.19388199'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'بوئين ومياندشت',
                'latitude'    => '33.08044434',
                'longitude'   => '50.15873337'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'بهاران شهر',
                'latitude'    => '32.51564407',
                'longitude'   => '51.54144287'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'بهارستان',
                'latitude'    => '32.48651123',
                'longitude'   => '51.77891159'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'پيربكران',
                'latitude'    => '32.46557617',
                'longitude'   => '51.55596161'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'تودشك',
                'latitude'    => '32.72826767',
                'longitude'   => '52.6791153'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'تيران',
                'latitude'    => '32.70440292',
                'longitude'   => '51.15317917'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'جندق',
                'latitude'    => '34.04131317',
                'longitude'   => '54.41446686'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'جوزدان',
                'latitude'    => '32.55489731',
                'longitude'   => '51.3718605'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'جوشقان وكامو',
                'latitude'    => '33.59946823',
                'longitude'   => '51.22826385'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'چادگان',
                'latitude'    => '32.77155685',
                'longitude'   => '50.62685776'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'چرمهين',
                'latitude'    => '32.33835983',
                'longitude'   => '51.19548798'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'چمگردان',
                'latitude'    => '32.3935051',
                'longitude'   => '51.33535004'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'حبيب آباد',
                'latitude'    => '32.82188797',
                'longitude'   => '51.77587128'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'حسن آباد',
                'latitude'    => '32.13663483',
                'longitude'   => '52.62354279'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'حنا',
                'latitude'    => '31.19758415',
                'longitude'   => '51.7256813'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'خالدآباد',
                'latitude'    => '33.70183563',
                'longitude'   => '51.98164368'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'خميني شهر',
                'latitude'    => '32.68333054',
                'longitude'   => '51.53359604'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'خوانسار',
                'latitude'    => '33.21422958',
                'longitude'   => '50.31887054'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'خور',
                'latitude'    => '33.77181625',
                'longitude'   => '55.08384323'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'خوراسگان',
                'latitude'    => '32.65510178',
                'longitude'   => '51.75954819'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'خورزوق',
                'latitude'    => '32.77672195',
                'longitude'   => '51.64983749'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'داران',
                'latitude'    => '32.9862175',
                'longitude'   => '50.40913773'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'دامنه',
                'latitude'    => '33.01579285',
                'longitude'   => '50.48777008'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'درچه پياز',
                'latitude'    => '32.60448074',
                'longitude'   => '51.5477066'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'دستگرد',
                'latitude'    => '32.80204773',
                'longitude'   => '51.66571426'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'دولت آباد',
                'latitude'    => '32.80472946',
                'longitude'   => '51.69356155'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'دهاقان',
                'latitude'    => '31.93313026',
                'longitude'   => '51.65417862'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'دهق',
                'latitude'    => '33.10338593',
                'longitude'   => '50.95917511'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ديزيچه',
                'latitude'    => '32.37792969',
                'longitude'   => '51.50416183'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'رزوه',
                'latitude'    => '32.83512878',
                'longitude'   => '50.56934357'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'رضوانشهر',
                'latitude'    => '32.69974136',
                'longitude'   => '51.10388184'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'زاينده رود',
                'latitude'    => '32.37821579',
                'longitude'   => '51.27326202'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'زرين شهر',
                'latitude'    => '32.39413071',
                'longitude'   => '51.38589478'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'زواره',
                'latitude'    => '33.44786072',
                'longitude'   => '52.48666382'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'زيباشهر',
                'latitude'    => '32.37941742',
                'longitude'   => '51.56482315'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'سده لنجان',
                'latitude'    => '32.37739182',
                'longitude'   => '51.31890488'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'سفيدشهر',
                'latitude'    => '34.12306213',
                'longitude'   => '51.35150146'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'سگزي',
                'latitude'    => '32.66856384',
                'longitude'   => '52.13046646'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'سميرم',
                'latitude'    => '31.41455269',
                'longitude'   => '51.5718689'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'شاپورآباد',
                'latitude'    => '32.84542084',
                'longitude'   => '51.74332047'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'شاهين شهر',
                'latitude'    => '32.86431885',
                'longitude'   => '51.55467606'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'شهرضا',
                'latitude'    => '31.97665977',
                'longitude'   => '51.84942627'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'طالخونچه',
                'latitude'    => '32.26208496',
                'longitude'   => '51.56427765'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'عسگران',
                'latitude'    => '32.86352539',
                'longitude'   => '50.85219955'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'علويچه',
                'latitude'    => '33.05480194',
                'longitude'   => '51.08145905'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'فرخي',
                'latitude'    => '33.8445816',
                'longitude'   => '54.94688034'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'فريدونشهر',
                'latitude'    => '32.93998718',
                'longitude'   => '50.13938522'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'فلاورجان',
                'latitude'    => '32.55626678',
                'longitude'   => '51.50629044'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'فولادشهر',
                'latitude'    => '32.48953247',
                'longitude'   => '51.40736771'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'قمصر',
                'latitude'    => '33.75201035',
                'longitude'   => '51.4306488'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'قهجاورستان',
                'latitude'    => '32.70313644',
                'longitude'   => '51.83500671'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'قهدريجان',
                'latitude'    => '32.57383728',
                'longitude'   => '51.45584869'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كاشان',
                'latitude'    => '33.98339462',
                'longitude'   => '51.43208313'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كركوند',
                'latitude'    => '32.3462944',
                'longitude'   => '51.43723679'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كليشادوسودرجان',
                'latitude'    => '32.54153824',
                'longitude'   => '51.5358963'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كمشچه',
                'latitude'    => '32.90576935',
                'longitude'   => '51.80904007'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كمه',
                'latitude'    => '31.06146622',
                'longitude'   => '51.59358215'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كوشك',
                'latitude'    => '32.6400795',
                'longitude'   => '51.50010681'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كوهپايه',
                'latitude'    => '32.71511078',
                'longitude'   => '52.43251801'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'كهريزسنگ',
                'latitude'    => '32.62624741',
                'longitude'   => '51.47711563'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'گرگاب',
                'latitude'    => '32.86576843',
                'longitude'   => '51.59765244'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'گزبرخوار',
                'latitude'    => '32.80460739',
                'longitude'   => '51.61882782'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'گلپايگان',
                'latitude'    => '33.45095062',
                'longitude'   => '50.28336334'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'گلدشت',
                'latitude'    => '32.6299057',
                'longitude'   => '51.44271851'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'گلشن',
                'latitude'    => '31.9292202',
                'longitude'   => '51.75250626'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'گلشهر',
                'latitude'    => '33.50588226',
                'longitude'   => '50.46561432'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'گوگد',
                'latitude'    => '33.47344589',
                'longitude'   => '50.34531403'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'لاي بيد',
                'latitude'    => '33.4586525',
                'longitude'   => '50.69414902'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'مباركه',
                'latitude'    => '32.34177399',
                'longitude'   => '51.50068283'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'محمدآباد',
                'latitude'    => '32.31945419',
                'longitude'   => '52.10391235'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'مشكات',
                'latitude'    => '34.17534637',
                'longitude'   => '51.26825714'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'منظريه',
                'latitude'    => '31.94329071',
                'longitude'   => '51.87212753'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'مهاباد',
                'latitude'    => '33.52765656',
                'longitude'   => '52.21732712'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ميمه',
                'latitude'    => '33.44674301',
                'longitude'   => '51.17034531'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'نائين',
                'latitude'    => '32.86044693',
                'longitude'   => '53.09561539'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'نجف آباد',
                'latitude'    => '32.63153458',
                'longitude'   => '51.35058975'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'نصرآباد',
                'latitude'    => '32.27835464',
                'longitude'   => '52.06196976'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'نطنز',
                'latitude'    => '33.50468063',
                'longitude'   => '51.9190712'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'نوش آباد',
                'latitude'    => '34.08082581',
                'longitude'   => '51.43794632'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'نياسر',
                'latitude'    => '33.97245026',
                'longitude'   => '51.14891052'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'نيك آباد',
                'latitude'    => '32.30278397',
                'longitude'   => '52.20520782'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ورزنه',
                'latitude'    => '32.4203186',
                'longitude'   => '52.65061188'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ورنامخواست',
                'latitude'    => '32.35495758',
                'longitude'   => '51.38011932'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'وزوان',
                'latitude'    => '33.41989136',
                'longitude'   => '51.18333817'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'ونك',
                'latitude'    => '31.52824211',
                'longitude'   => '51.32647324'
            ],
            [
                'province'    => 'اصفهان',
                'city'        => 'هرند',
                'latitude'    => '32.56386185',
                'longitude'   => '52.43729019'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'اشتهارد',
                'latitude'    => '35.7245903',
                'longitude'   => '50.3614769'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'آسارا',
                'latitude'    => '36.03144836',
                'longitude'   => '51.1738739'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'تنكمان',
                'latitude'    => '35.88940811',
                'longitude'   => '50.61488342'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'چهارباغ',
                'latitude'    => '35.83815384',
                'longitude'   => '50.8479805'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'سيف آباد',
                'latitude'    => '35.91147995',
                'longitude'   => '50.77053833'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'شهرجديدهشتگرد',
                'latitude'    => '35.98429489',
                'longitude'   => '50.74120331'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'طالقان',
                'latitude'    => '36.17299652',
                'longitude'   => '50.76848984'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'كرج',
                'latitude'    => '35.72472',
                'longitude'   => '50.95276642'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'كمال شهر',
                'latitude'    => '35.84603882',
                'longitude'   => '50.88404846'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'كوهسار',
                'latitude'    => '35.9556694',
                'longitude'   => '50.79353333'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'گرمدره',
                'latitude'    => '35.74454117',
                'longitude'   => '51.06885529'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'ماهدشت',
                'latitude'    => '35.72756195',
                'longitude'   => '50.80612946'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'محمدشهر',
                'latitude'    => '35.73664474',
                'longitude'   => '50.89955139'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'مشكين دشت',
                'latitude'    => '35.74838638',
                'longitude'   => '50.94078064'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'نظرآباد',
                'latitude'    => '35.95195007',
                'longitude'   => '50.60453415'
            ],
            [
                'province'    => 'البرز',
                'city'        => 'هشتگرد',
                'latitude'    => '35.95068359',
                'longitude'   => '50.68386078'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'اركواز',
                'latitude'    => '33.38710785',
                'longitude'   => '46.59772491'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'ايلام',
                'latitude'    => '33.57505417',
                'longitude'   => '46.41641617'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'ايوان',
                'latitude'    => '33.82935333',
                'longitude'   => '46.30612946'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'آبدانان',
                'latitude'    => '32.98835373',
                'longitude'   => '47.42409515'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'آسمان آباد',
                'latitude'    => '33.84938431',
                'longitude'   => '46.46562576'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'بدره',
                'latitude'    => '33.30717087',
                'longitude'   => '47.03833008'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'پهله',
                'latitude'    => '33.01145554',
                'longitude'   => '46.88355637'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'توحيد',
                'latitude'    => '33.7268219',
                'longitude'   => '47.06829453'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'چوار',
                'latitude'    => '33.69190979',
                'longitude'   => '46.29715347'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'دره شهر',
                'latitude'    => '33.14948654',
                'longitude'   => '47.38166046'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'دلگشا',
                'latitude'    => '33.40571213',
                'longitude'   => '46.59428787'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'دهلران',
                'latitude'    => '32.69020081',
                'longitude'   => '47.27085495'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'زرنه',
                'latitude'    => '33.92621231',
                'longitude'   => '46.1867218'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'سراب باغ',
                'latitude'    => '32.8966713',
                'longitude'   => '47.57263184'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'سرابله',
                'latitude'    => '33.76822662',
                'longitude'   => '46.56136703'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'صالح آباد',
                'latitude'    => '33.46877289',
                'longitude'   => '46.19106674'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'لومار',
                'latitude'    => '33.56718445',
                'longitude'   => '46.81135559'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'مورموري',
                'latitude'    => '32.72631836',
                'longitude'   => '47.67509842'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'موسيان',
                'latitude'    => '32.52018356',
                'longitude'   => '47.37707138'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'مهران',
                'latitude'    => '33.12202835',
                'longitude'   => '46.17288971'
            ],
            [
                'province'    => 'ايلام',
                'city'        => 'ميمه ',
                'latitude'    => '33.22662354',
                'longitude'   => '46.91864777'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'اسكو',
                'latitude'    => '37.91778183',
                'longitude'   => '46.12446976'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'اهر',
                'latitude'    => '38.47453308',
                'longitude'   => '47.07098389'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'ايلخچي',
                'latitude'    => '37.93674088',
                'longitude'   => '45.981987'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'آبش احمد',
                'latitude'    => '39.04013824',
                'longitude'   => '47.31736374'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'آذرشهر',
                'latitude'    => '37.76617813',
                'longitude'   => '45.97644806'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'آقكند',
                'latitude'    => '37.25337219',
                'longitude'   => '48.06547165'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'باسمنج',
                'latitude'    => '37.99435425',
                'longitude'   => '46.4747467'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'بخشايش',
                'latitude'    => '38.1318512',
                'longitude'   => '46.9474678'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'بستان آباد',
                'latitude'    => '37.83699417',
                'longitude'   => '46.83096313'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'بناب',
                'latitude'    => '37.34558868',
                'longitude'   => '46.06620026'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'بناب جديد',
                'latitude'    => '38.42330551',
                'longitude'   => '45.90700912'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'تبريز',
                'latitude'    => '38.0792923',
                'longitude'   => '46.28915024'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'ترك',
                'latitude'    => '37.60768509',
                'longitude'   => '47.76818848'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'تركمانچاي',
                'latitude'    => '37.58044052',
                'longitude'   => '47.39145279'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'تسوج',
                'latitude'    => '38.31055069',
                'longitude'   => '45.37041473'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'تيكمه داش',
                'latitude'    => '37.72838974',
                'longitude'   => '46.9458046'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'جلفا',
                'latitude'    => '38.93476486',
                'longitude'   => '45.63887024'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'خاروانا',
                'latitude'    => '38.68300247',
                'longitude'   => '46.16864014'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'خامنه',
                'latitude'    => '38.19393921',
                'longitude'   => '45.63295746'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'خراجو',
                'latitude'    => '37.31164932',
                'longitude'   => '46.53153229'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'خسروشهر',
                'latitude'    => '37.95437241',
                'longitude'   => '46.05068588'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'خمارلو',
                'latitude'    => '39.14801025',
                'longitude'   => '47.03475571'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'خواجه',
                'latitude'    => '38.1546936',
                'longitude'   => '46.58691025'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'دوزدوزان',
                'latitude'    => '37.94933319',
                'longitude'   => '47.1199646'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'زرنق',
                'latitude'    => '38.09184265',
                'longitude'   => '47.08170319'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'زنوز',
                'latitude'    => '38.58799362',
                'longitude'   => '45.83438873'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'سراب',
                'latitude'    => '37.94099808',
                'longitude'   => '47.53533173'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'سردرود',
                'latitude'    => '38.03178024',
                'longitude'   => '46.14791489'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'سيس',
                'latitude'    => '38.19407654',
                'longitude'   => '45.81408691'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'سيه رود',
                'latitude'    => '38.86951828',
                'longitude'   => '46.00170517'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'شبستر',
                'latitude'    => '38.18050003',
                'longitude'   => '45.70139313'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'شربيان',
                'latitude'    => '37.88155365',
                'longitude'   => '47.09996033'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'شرفخانه',
                'latitude'    => '38.17783737',
                'longitude'   => '45.49025726'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'شندآباد',
                'latitude'    => '38.14213562',
                'longitude'   => '45.62252045'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'شهرجديدسهند',
                'latitude'    => '37.94509125',
                'longitude'   => '46.12100983'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'صوفيان',
                'latitude'    => '38.27862167',
                'longitude'   => '45.98161697'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'عجب شير',
                'latitude'    => '37.47710037',
                'longitude'   => '45.89483261'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'قره آغاج',
                'latitude'    => '37.1291008',
                'longitude'   => '46.97224045'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'كشكسراي',
                'latitude'    => '38.46005249',
                'longitude'   => '45.56893539'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'كلوانق',
                'latitude'    => '38.10124969',
                'longitude'   => '46.99319077'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'كليبر',
                'latitude'    => '38.86484909',
                'longitude'   => '47.04078293'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'كوزه كنان',
                'latitude'    => '38.18380356',
                'longitude'   => '45.57749176'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'گوگان',
                'latitude'    => '37.76913452',
                'longitude'   => '45.90457153'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'ليلان',
                'latitude'    => '37.00645065',
                'longitude'   => '46.20556641'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'مراغه',
                'latitude'    => '37.38603592',
                'longitude'   => '46.23360443'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'مرند',
                'latitude'    => '38.43193817',
                'longitude'   => '45.77204514'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'ملكان',
                'latitude'    => '37.14253235',
                'longitude'   => '46.10037613'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'ممقان',
                'latitude'    => '37.83863449',
                'longitude'   => '45.97155762'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'مهربان',
                'latitude'    => '38.08159256',
                'longitude'   => '47.13167191'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'ميانه',
                'latitude'    => '37.4207077',
                'longitude'   => '47.71635056'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'نظركهريزي',
                'latitude'    => '37.34731293',
                'longitude'   => '46.76208496'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'وايقان',
                'latitude'    => '38.1308403',
                'longitude'   => '45.71278381'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'ورزقان',
                'latitude'    => '38.50870514',
                'longitude'   => '46.65034485'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'هاديشهر',
                'latitude'    => '38.8384285',
                'longitude'   => '45.66290665'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'هريس',
                'latitude'    => '38.24962234',
                'longitude'   => '47.11711884'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'هشترود',
                'latitude'    => '37.4707756',
                'longitude'   => '47.05966949'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'هوراند',
                'latitude'    => '38.85718918',
                'longitude'   => '47.36512756'
            ],
            [
                'province'    => 'آذربايجان شرقي',
                'city'        => 'يامچي',
                'latitude'    => '38.52165985',
                'longitude'   => '45.64025879'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'اروميه',
                'latitude'    => '37.53953934',
                'longitude'   => '45.05765152'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'اشنويه',
                'latitude'    => '37.04021072',
                'longitude'   => '45.10004425'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'ايواوغلي',
                'latitude'    => '38.71865463',
                'longitude'   => '45.21141052'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'آواجيق',
                'latitude'    => '39.33173752',
                'longitude'   => '44.1531105'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'باروق',
                'latitude'    => '36.9509697',
                'longitude'   => '46.31997681'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'بازرگان',
                'latitude'    => '39.39423752',
                'longitude'   => '44.38668823'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'بوكان',
                'latitude'    => '36.50798798',
                'longitude'   => '46.20666122'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'پلدشت',
                'latitude'    => '39.3478508',
                'longitude'   => '45.06837082'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'پيرانشهر',
                'latitude'    => '36.69393921',
                'longitude'   => '45.14119339'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'تازه شهر',
                'latitude'    => '38.17576599',
                'longitude'   => '44.69081116'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'تكاب',
                'latitude'    => '36.40422058',
                'longitude'   => '47.11016083'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'چهاربرج',
                'latitude'    => '37.12577057',
                'longitude'   => '45.97556305'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'خليفان',
                'latitude'    => '36.50708771',
                'longitude'   => '45.79647446'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'خوي',
                'latitude'    => '38.52029419',
                'longitude'   => '44.95465469'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'ديزج ديز',
                'latitude'    => '38.46089554',
                'longitude'   => '45.02344513'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'ربط',
                'latitude'    => '36.21127319',
                'longitude'   => '45.55174255'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'سردشت',
                'latitude'    => '36.15399551',
                'longitude'   => '45.47911453'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'سرو',
                'latitude'    => '37.72273254',
                'longitude'   => '44.65042877'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'سلماس',
                'latitude'    => '38.1987381',
                'longitude'   => '44.76602936'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'سيلوانه',
                'latitude'    => '37.42242813',
                'longitude'   => '44.85132217'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'سيمينه',
                'latitude'    => '36.72569656',
                'longitude'   => '46.15306854'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'سيه چشمه',
                'latitude'    => '39.06917953',
                'longitude'   => '44.3874588'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'شاهين دژ',
                'latitude'    => '36.67921448',
                'longitude'   => '46.56646347'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'شوط',
                'latitude'    => '39.21812439',
                'longitude'   => '44.77112198'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'فيرورق',
                'latitude'    => '38.57778168',
                'longitude'   => '44.8367157'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'قره ضياءالدين',
                'latitude'    => '38.88854218',
                'longitude'   => '45.02517319'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'قطور',
                'latitude'    => '38.47347641',
                'longitude'   => '44.40570068'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'قوشچي',
                'latitude'    => '37.99120712',
                'longitude'   => '45.03787613'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'كشاورز',
                'latitude'    => '36.83474731',
                'longitude'   => '46.35843277'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'گردكشانه',
                'latitude'    => '36.81188965',
                'longitude'   => '45.27393723'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'ماكو',
                'latitude'    => '39.2921524',
                'longitude'   => '44.49243546'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'محمديار',
                'latitude'    => '36.98139954',
                'longitude'   => '45.5221405'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'محمودآباد',
                'latitude'    => '36.71187592',
                'longitude'   => '46.51900864'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'مهاباد ',
                'latitude'    => '36.77022171',
                'longitude'   => '45.72841263'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'مياندوآب',
                'latitude'    => '36.96496201',
                'longitude'   => '46.1059494'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'ميرآباد',
                'latitude'    => '36.4030838',
                'longitude'   => '45.37486649'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'نالوس',
                'latitude'    => '36.98450851',
                'longitude'   => '45.1431427'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'نقده',
                'latitude'    => '36.9533844',
                'longitude'   => '45.38890457'
            ],
            [
                'province'    => 'آذربايجان غربي',
                'city'        => 'نوشين',
                'latitude'    => '37.73433685',
                'longitude'   => '45.05229187'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'امام حسن',
                'latitude'    => '29.83965492',
                'longitude'   => '50.2638855'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'انارستان',
                'latitude'    => '28.0325489',
                'longitude'   => '52.06646729'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'اهرم',
                'latitude'    => '28.88422394',
                'longitude'   => '51.27869415'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'آبپخش',
                'latitude'    => '29.35378647',
                'longitude'   => '51.06462097'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'آبدان',
                'latitude'    => '28.07392883',
                'longitude'   => '51.77064514'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'برازجان',
                'latitude'    => '29.2707119',
                'longitude'   => '51.21670914'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بردخون',
                'latitude'    => '28.06385231',
                'longitude'   => '51.47783661'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بردستان',
                'latitude'    => '27.87148285',
                'longitude'   => '51.95964432'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بندردير',
                'latitude'    => '27.83905983',
                'longitude'   => '51.93501282'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بندرديلم',
                'latitude'    => '30.05228233',
                'longitude'   => '50.16318893'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بندرريگ',
                'latitude'    => '29.4879055',
                'longitude'   => '50.62647629'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بندركنگان',
                'latitude'    => '27.83776283',
                'longitude'   => '52.06179428'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بندرگناوه',
                'latitude'    => '29.57365417',
                'longitude'   => '50.51854324'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بنك',
                'latitude'    => '27.87168884',
                'longitude'   => '52.02739716'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'بوشهر',
                'latitude'    => '28.92142677',
                'longitude'   => '50.83766937'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'تنگ ارم',
                'latitude'    => '29.15369034',
                'longitude'   => '51.52742004'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'جم',
                'latitude'    => '27.82435226',
                'longitude'   => '52.34095001'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'چغادك',
                'latitude'    => '28.98757172',
                'longitude'   => '51.02501678'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'خارك',
                'latitude'    => '29.23805237',
                'longitude'   => '50.31930542'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'خورموج',
                'latitude'    => '28.62718201',
                'longitude'   => '51.38252258'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'دالكي',
                'latitude'    => '29.42924118',
                'longitude'   => '51.29114914'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'دلوار',
                'latitude'    => '28.74599457',
                'longitude'   => '51.0701828'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'ريز',
                'latitude'    => '28.05557632',
                'longitude'   => '52.07707977'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'سعدآباد',
                'latitude'    => '29.38419533',
                'longitude'   => '51.116642'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'سيراف',
                'latitude'    => '27.66817284',
                'longitude'   => '52.33884811'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'شبانكاره',
                'latitude'    => '29.47254372',
                'longitude'   => '50.99406433'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'شنبه',
                'latitude'    => '28.39498138',
                'longitude'   => '51.76403046'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'عسلويه',
                'latitude'    => '27.47576141',
                'longitude'   => '52.60810852'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'كاكي',
                'latitude'    => '28.34020996',
                'longitude'   => '51.52267838'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'كلمه',
                'latitude'    => '28.94351768',
                'longitude'   => '51.45933151'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'نخل تقي',
                'latitude'    => '27.497015',
                'longitude'   => '52.58398819'
            ],
            [
                'province'    => 'بوشهر',
                'city'        => 'وحدتيه',
                'latitude'    => '29.48295784',
                'longitude'   => '51.23637772'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'ارجمند',
                'latitude'    => '35.81401825',
                'longitude'   => '52.51382446'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'اسلامشهر',
                'latitude'    => '35.50104523',
                'longitude'   => '51.22554779'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'انديشه',
                'latitude'    => '35.68739319',
                'longitude'   => '51.02239227'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'آبسرد',
                'latitude'    => '35.62170792',
                'longitude'   => '52.15012741'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'آبعلي',
                'latitude'    => '35.75962448',
                'longitude'   => '51.96295929'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'باغستان',
                'latitude'    => '35.634552',
                'longitude'   => '51.13315201'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'باقرشهر',
                'latitude'    => '35.52624512',
                'longitude'   => '51.40306091'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'بومهن',
                'latitude'    => '35.72784805',
                'longitude'   => '51.86883163'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'پاكدشت',
                'latitude'    => '35.47431183',
                'longitude'   => '51.67570114'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'پرديس',
                'latitude'    => '35.74234772',
                'longitude'   => '51.82140732'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'پيشوا',
                'latitude'    => '35.30555344',
                'longitude'   => '51.72243881'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'تجريش',
                'latitude'    => '35.81037903',
                'longitude'   => '51.46475983'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'تهران',
                'latitude'    => '35.70290756',
                'longitude'   => '51.34975815'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'جوادآباد',
                'latitude'    => '35.21086121',
                'longitude'   => '51.67318344'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'چهاردانگه',
                'latitude'    => '35.59637451',
                'longitude'   => '51.30456543'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'حسن آباد ',
                'latitude'    => '35.36765671',
                'longitude'   => '51.24298859'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'دماوند',
                'latitude'    => '35.69395447',
                'longitude'   => '52.04721832'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'رباط كريم',
                'latitude'    => '35.48320007',
                'longitude'   => '51.07991409'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'رودهن',
                'latitude'    => '35.73760986',
                'longitude'   => '51.91042328'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'ري',
                'latitude'    => '35.60319138',
                'longitude'   => '51.43870926'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'شاهدشهر',
                'latitude'    => '35.57186127',
                'longitude'   => '51.08738327'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'شريف آباد',
                'latitude'    => '35.42234039',
                'longitude'   => '51.78591919'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'شهريار',
                'latitude'    => '35.63301468',
                'longitude'   => '51.05970383'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'صالح آباد ',
                'latitude'    => '35.50494003',
                'longitude'   => '51.18913651'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'صباشهر',
                'latitude'    => '35.57875824',
                'longitude'   => '51.11426544'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'صفادشت',
                'latitude'    => '35.69135284',
                'longitude'   => '50.84069443'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'فردوسيه',
                'latitude'    => '35.60192871',
                'longitude'   => '51.06320953'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'فرون آباد',
                'latitude'    => '35.50527191',
                'longitude'   => '51.62708282'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'فشم',
                'latitude'    => '35.9017334',
                'longitude'   => '51.51983643'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'فيروزكوه',
                'latitude'    => '35.75455856',
                'longitude'   => '52.77387238'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'قدس',
                'latitude'    => '35.71248245',
                'longitude'   => '51.11289215'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'قرچك',
                'latitude'    => '35.41579437',
                'longitude'   => '51.58667374'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'كهريزك',
                'latitude'    => '35.52381516',
                'longitude'   => '51.35510254'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'كيلان',
                'latitude'    => '35.55614853',
                'longitude'   => '52.16287231'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'گلستان',
                'latitude'    => '35.51601028',
                'longitude'   => '51.16218948'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'لواسان',
                'latitude'    => '35.81940079',
                'longitude'   => '51.63729095'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'ملارد',
                'latitude'    => '35.65470123',
                'longitude'   => '50.97917557'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'نسيم شهر',
                'latitude'    => '35.55868912',
                'longitude'   => '51.16949081'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'نصيرآباد',
                'latitude'    => '35.4901886',
                'longitude'   => '51.14104462'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'وحيديه',
                'latitude'    => '35.60522079',
                'longitude'   => '51.02610397'
            ],
            [
                'province'    => 'تهران',
                'city'        => 'ورامين',
                'latitude'    => '35.32939529',
                'longitude'   => '51.63468552'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'اردل',
                'latitude'    => '31.99786568',
                'longitude'   => '50.66145325'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'آلوني',
                'latitude'    => '31.55220413',
                'longitude'   => '51.05701447'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'باباحيدر',
                'latitude'    => '32.32894516',
                'longitude'   => '50.46887589'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'بروجن',
                'latitude'    => '31.96679497',
                'longitude'   => '51.2894249'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'بلداجي',
                'latitude'    => '31.93704224',
                'longitude'   => '51.0526123'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'بن',
                'latitude'    => '32.54278183',
                'longitude'   => '50.74620819'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'جونقان',
                'latitude'    => '32.14295578',
                'longitude'   => '50.68659973'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'چلگرد',
                'latitude'    => '32.46727753',
                'longitude'   => '50.13004303'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'سامان',
                'latitude'    => '32.45157623',
                'longitude'   => '50.91045761'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'سفيددشت',
                'latitude'    => '32.12977219',
                'longitude'   => '51.18287659'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'سودجان',
                'latitude'    => '32.52112579',
                'longitude'   => '50.40035629'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'سورشجان',
                'latitude'    => '32.31581497',
                'longitude'   => '50.67885208'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'شلمزار',
                'latitude'    => '32.04661942',
                'longitude'   => '50.81704712'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'شهركرد',
                'latitude'    => '32.31618118',
                'longitude'   => '50.85820389'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'طاقانك',
                'latitude'    => '32.22312546',
                'longitude'   => '50.83694077'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'فارسان',
                'latitude'    => '32.25729752',
                'longitude'   => '50.56567764'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'فرادنبه',
                'latitude'    => '32.00857162',
                'longitude'   => '51.21474838'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'فرخ شهر',
                'latitude'    => '32.273983',
                'longitude'   => '50.97294617'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'كيان',
                'latitude'    => '32.28331375',
                'longitude'   => '50.88917542'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'گندمان',
                'latitude'    => '31.86305046',
                'longitude'   => '51.15338516'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'گهرو',
                'latitude'    => '32.00152588',
                'longitude'   => '50.88628769'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'لردگان',
                'latitude'    => '31.51081848',
                'longitude'   => '50.83008957'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'مال خليفه',
                'latitude'    => '31.28985977',
                'longitude'   => '51.26540756'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'ناغان',
                'latitude'    => '31.93432236',
                'longitude'   => '50.73060226'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'نافچ',
                'latitude'    => '32.42409515',
                'longitude'   => '50.78843689'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'نقنه',
                'latitude'    => '31.93233681',
                'longitude'   => '51.32884216'
            ],
            [
                'province'    => 'چهارمحال وبختياري',
                'city'        => 'هفشجان',
                'latitude'    => '32.22559357',
                'longitude'   => '50.79463196'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'ارسك',
                'latitude'    => '33.70366669',
                'longitude'   => '57.37181854'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'اسديه',
                'latitude'    => '32.91592789',
                'longitude'   => '60.02449036'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'اسفدن',
                'latitude'    => '33.64526749',
                'longitude'   => '59.77878571'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'اسلاميه',
                'latitude'    => '34.04231262',
                'longitude'   => '58.22000885'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'آرين شهر',
                'latitude'    => '33.33110046',
                'longitude'   => '59.23299789'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'آيسك',
                'latitude'    => '33.88665009',
                'longitude'   => '58.38159561'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'بشرويه',
                'latitude'    => '33.85281372',
                'longitude'   => '57.42244339'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'بيرجند',
                'latitude'    => '32.85693359',
                'longitude'   => '59.21954727'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'حاجي آباد',
                'latitude'    => '33.60467529',
                'longitude'   => '59.99753571'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'خضري دشت بياض',
                'latitude'    => '34.02294922',
                'longitude'   => '58.80654144'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'خوسف',
                'latitude'    => '32.77917099',
                'longitude'   => '58.88586426'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'زهان',
                'latitude'    => '33.41973877',
                'longitude'   => '59.8108902'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'سرايان',
                'latitude'    => '33.86037445',
                'longitude'   => '58.52231979'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'سربيشه',
                'latitude'    => '32.5763588',
                'longitude'   => '59.79834747'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'سه قلعه',
                'latitude'    => '33.66646957',
                'longitude'   => '58.39940262'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'شوسف',
                'latitude'    => '31.8033371',
                'longitude'   => '60.00886917'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'طبس مسينا',
                'latitude'    => '32.80108261',
                'longitude'   => '60.22293854'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'فردوس',
                'latitude'    => '34.01807785',
                'longitude'   => '58.17238617'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'قائن',
                'latitude'    => '33.72944641',
                'longitude'   => '59.18090439'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'قهستان',
                'latitude'    => '33.14633179',
                'longitude'   => '59.71578979'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'گزيك',
                'latitude'    => '33.00030136',
                'longitude'   => '60.22256088'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'محمد شهر',
                'latitude'    => '32.87498474',
                'longitude'   => '59.01773453'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'مود',
                'latitude'    => '32.7053299',
                'longitude'   => '59.52415085'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'نهبندان',
                'latitude'    => '31.54078674',
                'longitude'   => '60.03780746'
            ],
            [
                'province'    => 'خراسان جنوبي',
                'city'        => 'نيمبلوك',
                'latitude'    => '33.90110016',
                'longitude'   => '58.92943192'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'احمدآبادصولت',
                'latitude'    => '35.11553955',
                'longitude'   => '60.68938065'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'انابد',
                'latitude'    => '35.25112152',
                'longitude'   => '57.80986023'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'باجگيران',
                'latitude'    => '37.62411499',
                'longitude'   => '58.41529083'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'باخرز',
                'latitude'    => '34.98329163',
                'longitude'   => '60.3187561'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'بار',
                'latitude'    => '36.48887634',
                'longitude'   => '58.71915436'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'بايگ',
                'latitude'    => '35.37513733',
                'longitude'   => '59.03970718'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'بجستان',
                'latitude'    => '34.51548767',
                'longitude'   => '58.18186188'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'بردسكن',
                'latitude'    => '35.26028061',
                'longitude'   => '57.97204971'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'بيدخت',
                'latitude'    => '34.34629059',
                'longitude'   => '58.75733566'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'تايباد',
                'latitude'    => '34.73983765',
                'longitude'   => '60.77713776'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'تربت جام',
                'latitude'    => '35.24164581',
                'longitude'   => '60.62161636'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'تربت حيدريه',
                'latitude'    => '35.27845764',
                'longitude'   => '59.21302032'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'جغتاي',
                'latitude'    => '36.63965225',
                'longitude'   => '57.07720566'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'جنگل',
                'latitude'    => '34.70163727',
                'longitude'   => '59.22346878'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'چاپشلو',
                'latitude'    => '37.34742355',
                'longitude'   => '59.07718277'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'چكنه',
                'latitude'    => '36.81797028',
                'longitude'   => '58.50419617'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'چناران',
                'latitude'    => '36.64228058',
                'longitude'   => '59.12083817'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'خرو',
                'latitude'    => '36.13648987',
                'longitude'   => '59.02706909'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'خليل آباد',
                'latitude'    => '35.24498749',
                'longitude'   => '58.28516388'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'خواف',
                'latitude'    => '34.56286621',
                'longitude'   => '60.14429474'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'داورزن',
                'latitude'    => '36.35125732',
                'longitude'   => '56.87804031'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'درگز',
                'latitude'    => '37.44475174',
                'longitude'   => '59.10571289'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'درود',
                'latitude'    => '36.13573837',
                'longitude'   => '59.11236954'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'دولت آباد ',
                'latitude'    => '35.2820816',
                'longitude'   => '59.52212143'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'رباط سنگ',
                'latitude'    => '35.54469681',
                'longitude'   => '59.19417953'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'رشتخوار',
                'latitude'    => '34.97506714',
                'longitude'   => '59.62304688'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'رضويه',
                'latitude'    => '36.20550156',
                'longitude'   => '59.76985168'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'روداب',
                'latitude'    => '36.02081299',
                'longitude'   => '57.31240463'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'ريوش',
                'latitude'    => '35.47652435',
                'longitude'   => '58.46169281'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'سبزوار',
                'latitude'    => '36.20921326',
                'longitude'   => '57.68231964'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'سرخس',
                'latitude'    => '36.54865646',
                'longitude'   => '61.14860153'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'سفيدسنگ',
                'latitude'    => '35.66009521',
                'longitude'   => '60.09333038'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'سلامي',
                'latitude'    => '34.74436951',
                'longitude'   => '59.97647095'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'سلطان آباد',
                'latitude'    => '36.4027977',
                'longitude'   => '58.03989029'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'سنگان',
                'latitude'    => '34.39777374',
                'longitude'   => '60.25547791'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'شادمهر',
                'latitude'    => '35.17341995',
                'longitude'   => '59.03796387'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'شانديز',
                'latitude'    => '36.39521408',
                'longitude'   => '59.29819489'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'ششتمد',
                'latitude'    => '35.95957947',
                'longitude'   => '57.77022552'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'شهرآباد',
                'latitude'    => '35.14691162',
                'longitude'   => '57.93487167'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'شهرزو',
                'latitude'    => '36.74603653',
                'longitude'   => '59.92428207'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'صالح آباد  ',
                'latitude'    => '35.6879921',
                'longitude'   => '61.0904541'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'طرقبه',
                'latitude'    => '36.3042984',
                'longitude'   => '59.37632751'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'عشق آباد',
                'latitude'    => '36.03941727',
                'longitude'   => '58.68320847'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'فرهادگرد',
                'latitude'    => '35.75182724',
                'longitude'   => '59.7298851'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'فريمان',
                'latitude'    => '35.7040329',
                'longitude'   => '59.84827805'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'فيروزه',
                'latitude'    => '36.28560638',
                'longitude'   => '58.58901978'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'فيض آباد',
                'latitude'    => '35.01462173',
                'longitude'   => '58.7751236'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'قاسم آباد',
                'latitude'    => '34.35560226',
                'longitude'   => '59.86196899'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'قدمگاه',
                'latitude'    => '36.10589218',
                'longitude'   => '59.06046677'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'قلندرآباد',
                'latitude'    => '35.59896851',
                'longitude'   => '59.95104218'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'قوچان',
                'latitude'    => '37.1023674',
                'longitude'   => '58.51087189'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'كاخك',
                'latitude'    => '34.14867401',
                'longitude'   => '58.64451218'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'كاريز',
                'latitude'    => '34.81363678',
                'longitude'   => '60.82538223'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'كاشمر',
                'latitude'    => '35.2427597',
                'longitude'   => '58.45988083'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'كدكن',
                'latitude'    => '35.5858345',
                'longitude'   => '58.8789711'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'كلات',
                'latitude'    => '36.99456024',
                'longitude'   => '59.74988174'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'كندر',
                'latitude'    => '35.21245956',
                'longitude'   => '58.15077972'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'گلمكان',
                'latitude'    => '36.48210526',
                'longitude'   => '59.15949249'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'گناباد',
                'latitude'    => '34.35174942',
                'longitude'   => '58.68571091'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'لطف آباد',
                'latitude'    => '37.51523209',
                'longitude'   => '59.33473206'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'مزدآوند',
                'latitude'    => '36.15465164',
                'longitude'   => '60.52762985'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'مشهد',
                'latitude'    => '36.31043243',
                'longitude'   => '59.57567215'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'مشهدريزه',
                'latitude'    => '34.7951889',
                'longitude'   => '60.50761795'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'ملك آباد',
                'latitude'    => '35.99659729',
                'longitude'   => '59.59358597'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'نشتيفان',
                'latitude'    => '34.4343605',
                'longitude'   => '60.17642212'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'نصر آباد',
                'latitude'    => '35.41807556',
                'longitude'   => '60.31539917'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'نقاب',
                'latitude'    => '36.70854568',
                'longitude'   => '57.40815735'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'نوخندان',
                'latitude'    => '37.51934814',
                'longitude'   => '58.98891449'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'نيشابور',
                'latitude'    => '36.2079277',
                'longitude'   => '58.79402924'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'نيل شهر',
                'latitude'    => '35.12285614',
                'longitude'   => '60.77313232'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'همت آباد',
                'latitude'    => '36.2977829',
                'longitude'   => '58.4641571'
            ],
            [
                'province'    => 'خراسان رضوي',
                'city'        => 'يونسي',
                'latitude'    => '34.80538177',
                'longitude'   => '58.4372139'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'اسفراين',
                'latitude'    => '37.07266617',
                'longitude'   => '57.50966263'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'ايور',
                'latitude'    => '36.96881104',
                'longitude'   => '56.26078415'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'آشخانه',
                'latitude'    => '37.55817795',
                'longitude'   => '56.92396164'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'بجنورد',
                'latitude'    => '37.44678879',
                'longitude'   => '57.32745743'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'پيش قلعه',
                'latitude'    => '37.64860153',
                'longitude'   => '57.00147629'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'تيتكانلو',
                'latitude'    => '37.28055573',
                'longitude'   => '58.28951263'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'جاجرم',
                'latitude'    => '36.9556427',
                'longitude'   => '56.35777664'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'حصارگرمخان',
                'latitude'    => '37.51585388',
                'longitude'   => '57.48488617'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'درق',
                'latitude'    => '36.97206879',
                'longitude'   => '56.21450043'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'راز',
                'latitude'    => '37.93445587',
                'longitude'   => '57.10754776'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'سنخواست',
                'latitude'    => '37.09990311',
                'longitude'   => '56.85155869'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'شوقان',
                'latitude'    => '37.34163666',
                'longitude'   => '56.88708115'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'شيروان',
                'latitude'    => '37.40151596',
                'longitude'   => '57.92416763'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'صفي آباد',
                'latitude'    => '36.69550323',
                'longitude'   => '57.92702484'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'فاروج',
                'latitude'    => '37.22369385',
                'longitude'   => '58.2189064'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'قاضي',
                'latitude'    => '37.49500656',
                'longitude'   => '56.74865723'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'گرمه',
                'latitude'    => '36.97851944',
                'longitude'   => '56.30154037'
            ],
            [
                'province'    => 'خراسان شمالي',
                'city'        => 'لوجلي',
                'latitude'    => '37.60704041',
                'longitude'   => '57.8576355'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'اروندكنار',
                'latitude'    => '29.97808266',
                'longitude'   => '48.51682663'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'الوان',
                'latitude'    => '31.87410164',
                'longitude'   => '48.34111404'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'اميديه',
                'latitude'    => '30.7556076',
                'longitude'   => '49.71224976'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'انديمشك',
                'latitude'    => '32.42464447',
                'longitude'   => '48.37310028'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'اهواز',
                'latitude'    => '31.24201393',
                'longitude'   => '48.67127228'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'ايذه',
                'latitude'    => '31.83068466',
                'longitude'   => '49.86493683'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'آبادان',
                'latitude'    => '30.35600853',
                'longitude'   => '48.28005219'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'آغاجاري',
                'latitude'    => '30.69972038',
                'longitude'   => '49.82976913'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'باغ ملك',
                'latitude'    => '31.51330566',
                'longitude'   => '49.8904953'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'بستان',
                'latitude'    => '31.72163963',
                'longitude'   => '47.98114777'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'بندرامام خميني',
                'latitude'    => '30.43869972',
                'longitude'   => '49.07567215'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'بندرماهشهر',
                'latitude'    => '30.46946907',
                'longitude'   => '49.18000793'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'بهبهان',
                'latitude'    => '30.59626389',
                'longitude'   => '50.24160767'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'تركالكي',
                'latitude'    => '32.24267578',
                'longitude'   => '48.84632111'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'جايزان',
                'latitude'    => '30.87495041',
                'longitude'   => '49.85443497'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'جنت مكان',
                'latitude'    => '32.1843071',
                'longitude'   => '48.81614685'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'چغاميش',
                'latitude'    => '32.20948029',
                'longitude'   => '48.54555511'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'چمران',
                'latitude'    => '30.71356392',
                'longitude'   => '49.17686844'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'چوئبده',
                'latitude'    => '30.20004654',
                'longitude'   => '48.55318832'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'حر',
                'latitude'    => '32.14421844',
                'longitude'   => '48.39052582'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'حسينيه',
                'latitude'    => '32.66335297',
                'longitude'   => '48.24562073'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'حمزه',
                'latitude'    => '32.39622498',
                'longitude'   => '48.5785408'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'حميديه',
                'latitude'    => '31.48162079',
                'longitude'   => '48.43239975'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'خرمشهر',
                'latitude'    => '30.4448185',
                'longitude'   => '48.17879105'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'دارخوين',
                'latitude'    => '30.74493217',
                'longitude'   => '48.42924881'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'دزآب',
                'latitude'    => '32.28790665',
                'longitude'   => '48.42823792'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'دزفول',
                'latitude'    => '32.37889481',
                'longitude'   => '48.41930008'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'دهدز',
                'latitude'    => '31.70995522',
                'longitude'   => '50.28852844'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'رامشير',
                'latitude'    => '30.89275551',
                'longitude'   => '49.40882874'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'رامهرمز',
                'latitude'    => '31.25921631',
                'longitude'   => '49.60531235'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'رفيع',
                'latitude'    => '31.597435',
                'longitude'   => '47.89417267'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'زهره',
                'latitude'    => '30.46815491',
                'longitude'   => '49.6830101'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'سالند',
                'latitude'    => '32.49406815',
                'longitude'   => '48.83433533'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'سردشت ',
                'latitude'    => '30.32780647',
                'longitude'   => '50.21788025'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'سماله',
                'latitude'    => '32.19401169',
                'longitude'   => '48.85775375'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'سوسنگرد',
                'latitude'    => '31.55861473',
                'longitude'   => '48.18958664'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'شادگان',
                'latitude'    => '30.64126205',
                'longitude'   => '48.66353607'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'شاوور',
                'latitude'    => '32.0586853',
                'longitude'   => '48.3008728'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'شرافت',
                'latitude'    => '32.08808136',
                'longitude'   => '48.76771927'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'شوش',
                'latitude'    => '32.20674515',
                'longitude'   => '48.25742722'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'شوشتر',
                'latitude'    => '32.05464172',
                'longitude'   => '48.8347168'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'شيبان',
                'latitude'    => '31.38457489',
                'longitude'   => '48.78711319'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'صالح شهر',
                'latitude'    => '32.21367264',
                'longitude'   => '48.67218399'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'صالح مشطط',
                'latitude'    => '32.31357956',
                'longitude'   => '48.14806747'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'صفي آباد ',
                'latitude'    => '32.2478447',
                'longitude'   => '48.42150879'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'صيدون',
                'latitude'    => '31.36574364',
                'longitude'   => '50.0813942'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'قلعه تل',
                'latitude'    => '31.63149834',
                'longitude'   => '49.89017868'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'قلعه خواجه',
                'latitude'    => '32.20529938',
                'longitude'   => '49.44870377'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'گتوند',
                'latitude'    => '32.24721527',
                'longitude'   => '48.81782532'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'گوريه',
                'latitude'    => '31.85664177',
                'longitude'   => '48.75579071'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'لالي',
                'latitude'    => '32.32981873',
                'longitude'   => '49.09298325'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'مسجدسليمان',
                'latitude'    => '31.96466255',
                'longitude'   => '49.28633881'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'مشراگه',
                'latitude'    => '31.01070213',
                'longitude'   => '49.43793869'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'مقاومت',
                'latitude'    => '30.40689087',
                'longitude'   => '48.19532776'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'ملاثاني',
                'latitude'    => '31.58534813',
                'longitude'   => '48.88701248'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'ميانرود',
                'latitude'    => '32.22786713',
                'longitude'   => '48.42446136'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'ميداود',
                'latitude'    => '31.37246704',
                'longitude'   => '49.81366348'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'مينوشهر',
                'latitude'    => '30.36065483',
                'longitude'   => '48.21039581'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'ويس',
                'latitude'    => '31.48414421',
                'longitude'   => '48.8745079'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'هفتگل',
                'latitude'    => '31.44567871',
                'longitude'   => '49.53313828'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'هنديجان',
                'latitude'    => '30.22368622',
                'longitude'   => '49.71139908'
            ],
            [
                'province'    => 'خوزستان',
                'city'        => 'هويزه',
                'latitude'    => '31.46068192',
                'longitude'   => '48.07748795'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'ابهر',
                'latitude'    => '36.13613129',
                'longitude'   => '49.22454453'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'ارمغانخانه',
                'latitude'    => '36.97814941',
                'longitude'   => '48.37194061'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'آب بر',
                'latitude'    => '36.92785263',
                'longitude'   => '48.95616531'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'چورزق',
                'latitude'    => '36.99287796',
                'longitude'   => '48.77852631'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'حلب',
                'latitude'    => '36.29621124',
                'longitude'   => '48.06386185'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'خرمدره',
                'latitude'    => '36.2114563',
                'longitude'   => '49.19538879'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'دندي',
                'latitude'    => '36.55109406',
                'longitude'   => '47.62024307'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'زرين آباد',
                'latitude'    => '36.42923355',
                'longitude'   => '48.27695084'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'زرين رود',
                'latitude'    => '35.75621796',
                'longitude'   => '48.48109055'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'زنجان',
                'latitude'    => '36.67955399',
                'longitude'   => '48.49299622'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'سجاس',
                'latitude'    => '36.24058914',
                'longitude'   => '48.55308533'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'سلطانيه',
                'latitude'    => '36.4336319',
                'longitude'   => '48.79550552'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'سهرورد',
                'latitude'    => '36.07280731',
                'longitude'   => '48.43910217'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'صائين قلعه',
                'latitude'    => '36.30476761',
                'longitude'   => '49.07235336'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'قيدار',
                'latitude'    => '36.10856628',
                'longitude'   => '48.58998871'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'گرماب',
                'latitude'    => '35.84553909',
                'longitude'   => '48.20112228'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'ماه نشان',
                'latitude'    => '36.74358368',
                'longitude'   => '47.67064667'
            ],
            [
                'province'    => 'زنجان',
                'city'        => 'هيدج',
                'latitude'    => '36.25403976',
                'longitude'   => '49.12958527'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'اميريه',
                'latitude'    => '36.02804947',
                'longitude'   => '54.14220428'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'ايوانكي',
                'latitude'    => '35.34497452',
                'longitude'   => '52.06897736'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'آرادان',
                'latitude'    => '35.25164795',
                'longitude'   => '52.49753952'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'بسطام',
                'latitude'    => '36.48446274',
                'longitude'   => '55.00090027'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'بيارجمند',
                'latitude'    => '36.07866669',
                'longitude'   => '55.80743027'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'دامغان',
                'latitude'    => '36.16519928',
                'longitude'   => '54.344841'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'درجزين',
                'latitude'    => '35.64560699',
                'longitude'   => '53.33364105'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'ديباج',
                'latitude'    => '36.43052292',
                'longitude'   => '54.22891235'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'سرخه',
                'latitude'    => '35.46372604',
                'longitude'   => '53.21035767'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'سمنان',
                'latitude'    => '35.57711411',
                'longitude'   => '53.38192749'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'شاهرود',
                'latitude'    => '36.41208649',
                'longitude'   => '54.9724617'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'شهميرزاد',
                'latitude'    => '35.77023315',
                'longitude'   => '53.33358383'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'كلاته خيج',
                'latitude'    => '36.67056274',
                'longitude'   => '55.30048752'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'گرمسار',
                'latitude'    => '35.21782303',
                'longitude'   => '52.33362579'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'مجن',
                'latitude'    => '36.48022079',
                'longitude'   => '54.64797974'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'مهدي شهر',
                'latitude'    => '35.69964981',
                'longitude'   => '53.34587097'
            ],
            [
                'province'    => 'سمنان',
                'city'        => 'ميامي',
                'latitude'    => '36.40932465',
                'longitude'   => '55.65165329'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'اديمي',
                'latitude'    => '31.11745262',
                'longitude'   => '61.40764236'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'اسپكه',
                'latitude'    => '26.83735847',
                'longitude'   => '60.17232513'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'ايرانشهر',
                'latitude'    => '27.20416451',
                'longitude'   => '60.67512894'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'بزمان',
                'latitude'    => '27.85034561',
                'longitude'   => '60.1754837'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'بمپور',
                'latitude'    => '27.19374084',
                'longitude'   => '60.45621872'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'بنت',
                'latitude'    => '26.28682327',
                'longitude'   => '59.52267075'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'بنجار',
                'latitude'    => '31.04289436',
                'longitude'   => '61.56745148'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'پيشين',
                'latitude'    => '26.07985497',
                'longitude'   => '61.74840164'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'جالق',
                'latitude'    => '27.58278656',
                'longitude'   => '62.70072556'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'چاه بهار',
                'latitude'    => '25.29451942',
                'longitude'   => '60.64770126'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'خاش',
                'latitude'    => '28.20943642',
                'longitude'   => '61.20286179'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'دوست محمد',
                'latitude'    => '31.14273262',
                'longitude'   => '61.79280853'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'راسك',
                'latitude'    => '26.23325157',
                'longitude'   => '61.40544128'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'زابل',
                'latitude'    => '31.00886726',
                'longitude'   => '61.49380875'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'زابلي',
                'latitude'    => '27.11458778',
                'longitude'   => '61.67259979'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'زاهدان',
                'latitude'    => '29.48213577',
                'longitude'   => '60.85205078'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'زرآباد',
                'latitude'    => '25.5872364',
                'longitude'   => '59.39628983'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'زهك',
                'latitude'    => '30.89316177',
                'longitude'   => '61.68292618'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'سراوان',
                'latitude'    => '27.36134338',
                'longitude'   => '62.33307266'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'سرباز',
                'latitude'    => '26.63323593',
                'longitude'   => '61.25806046'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'سوران',
                'latitude'    => '27.28602982',
                'longitude'   => '61.99488068'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'سيركان',
                'latitude'    => '26.82987213',
                'longitude'   => '62.63848114'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'علي اكبر',
                'latitude'    => '30.93907928',
                'longitude'   => '61.32796478'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'فنوج',
                'latitude'    => '26.57572746',
                'longitude'   => '59.6449852'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'قصرقند',
                'latitude'    => '26.2130394',
                'longitude'   => '60.74167633'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'كنارك',
                'latitude'    => '25.35951996',
                'longitude'   => '60.39698029'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'گشت',
                'latitude'    => '27.78860664',
                'longitude'   => '61.95108795'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'گلمورتي',
                'latitude'    => '27.48122978',
                'longitude'   => '59.44732285'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'محمدان',
                'latitude'    => '27.19989777',
                'longitude'   => '60.56081772'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'محمد آباد',
                'latitude'    => '30.87292671',
                'longitude'   => '61.46186447'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'محمدي',
                'latitude'    => '27.32683372',
                'longitude'   => '62.39016724'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'ميرجاوه',
                'latitude'    => '29.01564407',
                'longitude'   => '61.44954681'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'نصرت آباد',
                'latitude'    => '29.85798264',
                'longitude'   => '59.97365952'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'نگور',
                'latitude'    => '25.38880539',
                'longitude'   => '61.13994217'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'نوك آباد',
                'latitude'    => '28.53910828',
                'longitude'   => '60.75888062'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'نيك شهر',
                'latitude'    => '26.23455238',
                'longitude'   => '60.22686005'
            ],
            [
                'province'    => 'سيستان وبلوچستان',
                'city'        => 'هيدوج',
                'latitude'    => '27.00206184',
                'longitude'   => '62.11936951'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'اردكان',
                'latitude'    => '30.2320652',
                'longitude'   => '51.99205017'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'ارسنجان',
                'latitude'    => '29.90981102',
                'longitude'   => '53.30069733'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'استهبان',
                'latitude'    => '29.12606621',
                'longitude'   => '54.04462814'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'اسير',
                'latitude'    => '27.72283173',
                'longitude'   => '52.66568756'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'اشكنان',
                'latitude'    => '27.22553635',
                'longitude'   => '53.60817337'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'افزر',
                'latitude'    => '28.3462944',
                'longitude'   => '52.96562576'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'اقليد',
                'latitude'    => '30.90192032',
                'longitude'   => '52.70526123'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'امام شهر',
                'latitude'    => '28.44523621',
                'longitude'   => '53.15060425'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'اوز',
                'latitude'    => '27.75788116',
                'longitude'   => '54.01237869'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'اهل',
                'latitude'    => '27.21099472',
                'longitude'   => '53.65979385'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'ايج',
                'latitude'    => '29.02017212',
                'longitude'   => '54.24538422'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'ايزدخواست',
                'latitude'    => '31.51700974',
                'longitude'   => '52.12493515'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'آباده',
                'latitude'    => '31.14298248',
                'longitude'   => '52.65011597'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'آباده طشك',
                'latitude'    => '29.80986214',
                'longitude'   => '53.73068619'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'باب انار',
                'latitude'    => '28.96844482',
                'longitude'   => '53.20914459'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'بالاده',
                'latitude'    => '29.28499222',
                'longitude'   => '51.9389267'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'بنارويه',
                'latitude'    => '28.08661079',
                'longitude'   => '54.04563141'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'بوانات',
                'latitude'    => '30.46093559',
                'longitude'   => '53.63896561'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'بهمن',
                'latitude'    => '31.19485855',
                'longitude'   => '52.4853096'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'بيرم',
                'latitude'    => '27.43091202',
                'longitude'   => '53.51512146'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'بيضا',
                'latitude'    => '29.97089386',
                'longitude'   => '52.39775848'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'جنت شهر',
                'latitude'    => '28.65347481',
                'longitude'   => '54.68517685'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'جويم',
                'latitude'    => '28.26154518',
                'longitude'   => '53.97995377'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'جهرم',
                'latitude'    => '28.49491882',
                'longitude'   => '53.5668869'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'حاجي آباد ',
                'latitude'    => '28.35749626',
                'longitude'   => '54.42031479'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'حسامي',
                'latitude'    => '29.96754265',
                'longitude'   => '53.87240601'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'حسن آباد  ',
                'latitude'    => '30.51979637',
                'longitude'   => '52.45603943'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'خانه زنيان',
                'latitude'    => '29.67118645',
                'longitude'   => '52.14970398'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'خاوران',
                'latitude'    => '28.93893814',
                'longitude'   => '53.31309509'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'خرامه',
                'latitude'    => '29.49304962',
                'longitude'   => '53.31003189'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'خشت',
                'latitude'    => '29.55426025',
                'longitude'   => '51.33544159'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'خنج',
                'latitude'    => '27.89233208',
                'longitude'   => '53.42961502'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'خور ',
                'latitude'    => '27.64462852',
                'longitude'   => '54.34395218'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'خومه زار',
                'latitude'    => '30.00632477',
                'longitude'   => '51.57812119'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'داراب',
                'latitude'    => '28.75426102',
                'longitude'   => '54.54712296'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'داريان',
                'latitude'    => '29.56033134',
                'longitude'   => '52.92008972'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'دبيران',
                'latitude'    => '28.39616203',
                'longitude'   => '54.18682861'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'دژكرد',
                'latitude'    => '30.71361351',
                'longitude'   => '51.95794296'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'دوبرجي',
                'latitude'    => '28.30734062',
                'longitude'   => '55.19250488'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'دوزه',
                'latitude'    => '28.70108604',
                'longitude'   => '52.95849228'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'دهرم',
                'latitude'    => '28.49329376',
                'longitude'   => '52.30422211'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'رامجرد',
                'latitude'    => '30.0748806',
                'longitude'   => '52.59263611'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'رونيز',
                'latitude'    => '29.19205666',
                'longitude'   => '53.76940155'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'زاهدشهر',
                'latitude'    => '28.74662971',
                'longitude'   => '53.80464935'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'زرقان',
                'latitude'    => '29.77383232',
                'longitude'   => '52.72177887'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'سده',
                'latitude'    => '30.71057892',
                'longitude'   => '52.17611694'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'سروستان',
                'latitude'    => '29.26302719',
                'longitude'   => '53.22387314'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'سعادت شهر',
                'latitude'    => '30.07427406',
                'longitude'   => '53.13613129'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'سورمق',
                'latitude'    => '31.03448868',
                'longitude'   => '52.83955765'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'سيدان',
                'latitude'    => '30.0044632',
                'longitude'   => '53.00567627'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'ششده',
                'latitude'    => '28.94964218',
                'longitude'   => '53.99575806'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'شهر جديد صدرا',
                'latitude'    => '29.80101395',
                'longitude'   => '52.50849533'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'شهرپير',
                'latitude'    => '28.3116951',
                'longitude'   => '54.33503342'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'شيراز',
                'latitude'    => '29.55689049',
                'longitude'   => '52.5291214'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'صغاد',
                'latitude'    => '31.19107056',
                'longitude'   => '52.51543427'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'صفاشهر',
                'latitude'    => '30.61366653',
                'longitude'   => '53.19872284'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'علامرودشت',
                'latitude'    => '27.62446213',
                'longitude'   => '53.00065994'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'عمادده',
                'latitude'    => '27.44464493',
                'longitude'   => '53.86183548'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'فدامي',
                'latitude'    => '28.21680832',
                'longitude'   => '55.13514328'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'فراشبند',
                'latitude'    => '28.85889053',
                'longitude'   => '52.09375'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'فسا',
                'latitude'    => '28.91972542',
                'longitude'   => '53.6376915'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'فيروزآباد',
                'latitude'    => '28.84885597',
                'longitude'   => '52.56980133'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'قادرآباد',
                'latitude'    => '30.28066635',
                'longitude'   => '53.25508881'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'قائميه',
                'latitude'    => '29.84431648',
                'longitude'   => '51.59110641'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'قطب آباد',
                'latitude'    => '28.64067459',
                'longitude'   => '53.63947296'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'قطرويه',
                'latitude'    => '29.14603615',
                'longitude'   => '54.70355225'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'قير',
                'latitude'    => '28.474617',
                'longitude'   => '53.04390335'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'كارزين',
                'latitude'    => '28.4087162',
                'longitude'   => '53.10852432'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'كازرون',
                'latitude'    => '29.60374641',
                'longitude'   => '51.65179825'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'كامفيروز',
                'latitude'    => '30.32198334',
                'longitude'   => '52.19429398'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'كره اي',
                'latitude'    => '30.02972603',
                'longitude'   => '53.71575546'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'كنارتخته',
                'latitude'    => '29.53391266',
                'longitude'   => '51.39491653'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'كوار',
                'latitude'    => '29.19608498',
                'longitude'   => '52.68671799'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'كوهنجان',
                'latitude'    => '29.2311306',
                'longitude'   => '52.95619202'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'گراش',
                'latitude'    => '27.66714287',
                'longitude'   => '54.14503479'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'گله دار',
                'latitude'    => '27.64880753',
                'longitude'   => '52.66020584'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'لار',
                'latitude'    => '27.6426487',
                'longitude'   => '54.32035828'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'لامرد',
                'latitude'    => '27.3299675',
                'longitude'   => '53.18791962'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'لپوئي',
                'latitude'    => '29.79907227',
                'longitude'   => '52.65207291'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'لطيفي',
                'latitude'    => '27.68995857',
                'longitude'   => '54.38670349'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'مبارك آباد',
                'latitude'    => '28.36018181',
                'longitude'   => '53.32870102'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'مرودشت',
                'latitude'    => '29.85951042',
                'longitude'   => '52.81027222'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'مشكان',
                'latitude'    => '29.47281647',
                'longitude'   => '54.34591293'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'مصيري',
                'latitude'    => '30.24431038',
                'longitude'   => '51.52890396'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'مهر',
                'latitude'    => '27.55045891',
                'longitude'   => '52.8838501'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'ميمند',
                'latitude'    => '28.86828804',
                'longitude'   => '52.75091553'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'نوبندگان',
                'latitude'    => '28.85382271',
                'longitude'   => '53.8259201'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'نوجين',
                'latitude'    => '29.12537384',
                'longitude'   => '52.01348877'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'نودان',
                'latitude'    => '29.80166054',
                'longitude'   => '51.69422913'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'نورآباد',
                'latitude'    => '30.11481667',
                'longitude'   => '51.52225113'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'ني ريز',
                'latitude'    => '29.19792175',
                'longitude'   => '54.32265472'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'وراوي',
                'latitude'    => '27.46503258',
                'longitude'   => '53.05218506'
            ],
            [
                'province'    => 'فارس',
                'city'        => 'هماشهر',
                'latitude'    => '30.1154232',
                'longitude'   => '52.087677'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'ارداق',
                'latitude'    => '36.05335617',
                'longitude'   => '49.82441711'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'اسفرورين',
                'latitude'    => '35.93508148',
                'longitude'   => '49.7502861'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'اقباليه',
                'latitude'    => '36.23208618',
                'longitude'   => '49.92666245'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'الوند',
                'latitude'    => '36.17034149',
                'longitude'   => '50.0741539'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'آبگرم',
                'latitude'    => '35.75740051',
                'longitude'   => '49.2872963'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'آبيك',
                'latitude'    => '36.03989029',
                'longitude'   => '50.52969742'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'آوج',
                'latitude'    => '35.57978058',
                'longitude'   => '49.2218399'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'بوئين زهرا',
                'latitude'    => '35.75715256',
                'longitude'   => '50.06196976'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'بيدستان',
                'latitude'    => '36.23126602',
                'longitude'   => '50.12140274'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'تاكستان',
                'latitude'    => '36.07141876',
                'longitude'   => '49.69615936'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'خاكعلي',
                'latitude'    => '36.12889099',
                'longitude'   => '50.17577744'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'خرمدشت',
                'latitude'    => '35.9298439',
                'longitude'   => '49.51174545'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'دانسفهان',
                'latitude'    => '35.81162643',
                'longitude'   => '49.74360275'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'رازميان',
                'latitude'    => '36.53649139',
                'longitude'   => '50.21233749'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'سگزآباد',
                'latitude'    => '35.76412964',
                'longitude'   => '49.9394722'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'سيردان',
                'latitude'    => '36.65159225',
                'longitude'   => '49.18821335'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'شال',
                'latitude'    => '35.89759827',
                'longitude'   => '49.76836777'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'شريفيه',
                'latitude'    => '36.2034111',
                'longitude'   => '50.1513443'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'ضياءآباد',
                'latitude'    => '35.99550629',
                'longitude'   => '49.44933701'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'قزوين',
                'latitude'    => '36.28174591',
                'longitude'   => '50.00196838'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'كوهين',
                'latitude'    => '36.37286758',
                'longitude'   => '49.65810776'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'محمديه',
                'latitude'    => '36.2240181',
                'longitude'   => '50.18258667'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'محمودآبادنمونه',
                'latitude'    => '36.29042053',
                'longitude'   => '49.9026413'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'معلم كلايه',
                'latitude'    => '36.44727707',
                'longitude'   => '50.47819519'
            ],
            [
                'province'    => 'قزوين',
                'city'        => 'نرجه',
                'latitude'    => '35.99202347',
                'longitude'   => '49.62033081'
            ],
            [
                'province'    => 'قم',
                'city'        => 'جعفريه',
                'latitude'    => '34.77463913',
                'longitude'   => '50.51660538'
            ],
            [
                'province'    => 'قم',
                'city'        => 'دستجرد',
                'latitude'    => '34.55284882',
                'longitude'   => '50.24821854'
            ],
            [
                'province'    => 'قم',
                'city'        => 'سلفچگان',
                'latitude'    => '34.47826385',
                'longitude'   => '50.45762634'
            ],
            [
                'province'    => 'قم',
                'city'        => 'قم',
                'latitude'    => '34.59394836',
                'longitude'   => '50.87429047'
            ],
            [
                'province'    => 'قم',
                'city'        => 'قنوات',
                'latitude'    => '34.61101151',
                'longitude'   => '51.02547073'
            ],
            [
                'province'    => 'قم',
                'city'        => 'كهك',
                'latitude'    => '34.39292526',
                'longitude'   => '50.86410904'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'آرمرده',
                'latitude'    => '35.92939377',
                'longitude'   => '45.79673004'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'بابارشاني',
                'latitude'    => '35.67422485',
                'longitude'   => '47.79747391'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'بانه',
                'latitude'    => '35.99573135',
                'longitude'   => '45.88861465'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'بلبان آباد',
                'latitude'    => '35.13714218',
                'longitude'   => '47.32099152'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'بوئين سفلي',
                'latitude'    => '35.9389801',
                'longitude'   => '45.93625259'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'بيجار',
                'latitude'    => '35.88286209',
                'longitude'   => '47.61825943'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'چناره',
                'latitude'    => '35.63011932',
                'longitude'   => '46.3091011'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'دزج',
                'latitude'    => '35.06471634',
                'longitude'   => '47.96392441'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'دلبران',
                'latitude'    => '35.23840332',
                'longitude'   => '47.98857117'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'دهگلان',
                'latitude'    => '35.27833557',
                'longitude'   => '47.41890335'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'ديواندره',
                'latitude'    => '35.91249084',
                'longitude'   => '47.02490616'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'زرينه',
                'latitude'    => '36.06072617',
                'longitude'   => '46.91876602'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'سروآباد',
                'latitude'    => '35.3117981',
                'longitude'   => '46.36698914'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'سريش آباد',
                'latitude'    => '35.24881363',
                'longitude'   => '47.77849197'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'سقز',
                'latitude'    => '36.24411774',
                'longitude'   => '46.27292252'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'سنندج',
                'latitude'    => '35.24031448',
                'longitude'   => '47.00640869'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'شويشه',
                'latitude'    => '35.3529129',
                'longitude'   => '46.67845535'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'صاحب',
                'latitude'    => '36.20309448',
                'longitude'   => '46.46185684'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'قروه',
                'latitude'    => '35.16360092',
                'longitude'   => '47.81004333'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'كامياران',
                'latitude'    => '34.79716873',
                'longitude'   => '46.93946457'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'كاني دينار',
                'latitude'    => '35.46753311',
                'longitude'   => '46.20333862'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'كاني سور',
                'latitude'    => '36.05905914',
                'longitude'   => '45.74882126'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'مريوان',
                'latitude'    => '35.51787186',
                'longitude'   => '46.18096924'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'موچش',
                'latitude'    => '35.05914307',
                'longitude'   => '47.15448761'
            ],
            [
                'province'    => 'كردستان',
                'city'        => 'ياسوكند',
                'latitude'    => '36.28339386',
                'longitude'   => '47.74681473'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'اختيارآباد',
                'latitude'    => '30.31972313',
                'longitude'   => '56.91827774'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'ارزوئيه',
                'latitude'    => '28.45792961',
                'longitude'   => '56.36352539'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'امين شهر',
                'latitude'    => '30.8438282',
                'longitude'   => '55.33936691'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'انار',
                'latitude'    => '30.87254906',
                'longitude'   => '55.27162933'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'اندوهجرد',
                'latitude'    => '30.23103142',
                'longitude'   => '57.75406265'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'باغين',
                'latitude'    => '30.18938255',
                'longitude'   => '56.81322479'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'بافت',
                'latitude'    => '29.23217583',
                'longitude'   => '56.59699249'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'بردسير',
                'latitude'    => '29.92410088',
                'longitude'   => '56.57876968'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'بروات',
                'latitude'    => '29.04700661',
                'longitude'   => '58.40858841'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'بزنجان',
                'latitude'    => '29.25483513',
                'longitude'   => '56.69428253'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'بم',
                'latitude'    => '29.06779861',
                'longitude'   => '58.34861755'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'بهرمان',
                'latitude'    => '30.90431786',
                'longitude'   => '55.72738647'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'پاريز',
                'latitude'    => '29.87262154',
                'longitude'   => '55.75054169'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'جبالبارز',
                'latitude'    => '28.90602112',
                'longitude'   => '57.88274765'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'جوپار',
                'latitude'    => '30.05652428',
                'longitude'   => '57.11240387'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'جوزم',
                'latitude'    => '30.51511955',
                'longitude'   => '55.03173447'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'جيرفت',
                'latitude'    => '28.68039322',
                'longitude'   => '57.74197388'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'چترود',
                'latitude'    => '30.60101891',
                'longitude'   => '56.90916824'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'خاتون آباد',
                'latitude'    => '29.9971447',
                'longitude'   => '55.41994476'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'خانوك',
                'latitude'    => '30.71772003',
                'longitude'   => '56.77554321'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'خورسند',
                'latitude'    => '30.15340042',
                'longitude'   => '55.08758163'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'درب بهشت',
                'latitude'    => '29.23355484',
                'longitude'   => '57.33667374'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'دوساري',
                'latitude'    => '28.42318916',
                'longitude'   => '57.9446907'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'دهج',
                'latitude'    => '30.68716621',
                'longitude'   => '54.87817764'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'رابر',
                'latitude'    => '29.28990936',
                'longitude'   => '56.91295624'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'راور',
                'latitude'    => '31.24226189',
                'longitude'   => '56.79842377'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'راين',
                'latitude'    => '29.59888077',
                'longitude'   => '57.43511581'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'رفسنجان',
                'latitude'    => '30.37527657',
                'longitude'   => '55.98764801'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'رودبار',
                'latitude'    => '28.0208168',
                'longitude'   => '57.99457932'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'ريحان شهر',
                'latitude'    => '30.75133705',
                'longitude'   => '56.73608017'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'زرند',
                'latitude'    => '30.81172752',
                'longitude'   => '56.55187225'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'زنگي آباد',
                'latitude'    => '30.41259766',
                'longitude'   => '56.91473389'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'زيدآباد',
                'latitude'    => '29.59856987',
                'longitude'   => '55.53490829'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'سرچشمه',
                'latitude'    => '29.99733734',
                'longitude'   => '55.79472351'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'سيرجان',
                'latitude'    => '29.43761635',
                'longitude'   => '55.67490768'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'شهداد',
                'latitude'    => '30.41668129',
                'longitude'   => '57.70170975'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'شهربابك',
                'latitude'    => '30.11832619',
                'longitude'   => '55.12078857'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'صفائيه',
                'latitude'    => '30.82782364',
                'longitude'   => '55.81137848'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'عنبرآباد',
                'latitude'    => '28.47891808',
                'longitude'   => '57.84252548'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'فارياب',
                'latitude'    => '28.09708405',
                'longitude'   => '57.22827911'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'فهرج',
                'latitude'    => '28.94784355',
                'longitude'   => '58.88511276'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'قلعه گنج',
                'latitude'    => '27.51635361',
                'longitude'   => '57.8829689'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'كاظم آباد',
                'latitude'    => '30.56055641',
                'longitude'   => '56.84365845'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'كرمان',
                'latitude'    => '30.28140259',
                'longitude'   => '57.06418228'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'كشكوئيه',
                'latitude'    => '30.5305233',
                'longitude'   => '55.64461899'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'كوهبنان',
                'latitude'    => '31.41319847',
                'longitude'   => '56.27933502'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'كهنوج',
                'latitude'    => '27.92245483',
                'longitude'   => '57.69707489'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'كيانشهر',
                'latitude'    => '31.15600395',
                'longitude'   => '56.38111115'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'گلباف',
                'latitude'    => '29.88442039',
                'longitude'   => '57.73108673'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'گلزار',
                'latitude'    => '29.7112484',
                'longitude'   => '57.04011154'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'لاله زار',
                'latitude'    => '29.52295113',
                'longitude'   => '56.81635284'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'ماهان',
                'latitude'    => '30.05790901',
                'longitude'   => '57.28974533'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'محمد آباد ',
                'latitude'    => '28.64166832',
                'longitude'   => '59.0174675'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'محي آباد',
                'latitude'    => '30.07167435',
                'longitude'   => '57.23011017'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'مردهك',
                'latitude'    => '28.34990883',
                'longitude'   => '58.15991974'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'منوجان',
                'latitude'    => '27.40366745',
                'longitude'   => '57.49675751'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'نجف شهر',
                'latitude'    => '29.39123344',
                'longitude'   => '55.72056961'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'نرماشير',
                'latitude'    => '28.95152855',
                'longitude'   => '58.69940567'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'نظام شهر',
                'latitude'    => '28.91307068',
                'longitude'   => '58.55143356'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'نگار',
                'latitude'    => '29.85721779',
                'longitude'   => '56.80291748'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'نودژ',
                'latitude'    => '27.52715683',
                'longitude'   => '57.44942474'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'هجدك',
                'latitude'    => '30.75760651',
                'longitude'   => '56.99620438'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'هماشهر ',
                'latitude'    => '29.6626358',
                'longitude'   => '55.80966568'
            ],
            [
                'province'    => 'كرمان',
                'city'        => 'يزدان شهر',
                'latitude'    => '30.8664341',
                'longitude'   => '56.37589264'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'ازگله',
                'latitude'    => '34.83340073',
                'longitude'   => '45.84191895'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'اسلام آبادغرب',
                'latitude'    => '34.10961533',
                'longitude'   => '46.52056885'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'باينگان',
                'latitude'    => '34.98140717',
                'longitude'   => '46.27041245'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'بيستون',
                'latitude'    => '34.39458466',
                'longitude'   => '47.44768524'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'پاوه',
                'latitude'    => '35.02669144',
                'longitude'   => '46.36696625'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'تازه آباد',
                'latitude'    => '34.73862076',
                'longitude'   => '46.15161514'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'جوانرود',
                'latitude'    => '34.80616379',
                'longitude'   => '46.49219131'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'حميل',
                'latitude'    => '33.93801117',
                'longitude'   => '46.7728653'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'رباط',
                'latitude'    => '34.26859283',
                'longitude'   => '46.80641174'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'روانسر',
                'latitude'    => '34.71617889',
                'longitude'   => '46.65607071'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'سرپل ذهاب',
                'latitude'    => '34.44868088',
                'longitude'   => '45.86302567'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'سرمست',
                'latitude'    => '34.02695465',
                'longitude'   => '46.33175278'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'سطر',
                'latitude'    => '34.81338882',
                'longitude'   => '47.46087646'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'سنقر',
                'latitude'    => '34.76270676',
                'longitude'   => '47.59963226'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'سومار',
                'latitude'    => '33.86553574',
                'longitude'   => '45.64097214'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'شاهو',
                'latitude'    => '34.93078995',
                'longitude'   => '46.46110916'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'صحنه',
                'latitude'    => '34.47551727',
                'longitude'   => '47.68521881'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'قصرشيرين',
                'latitude'    => '34.50753021',
                'longitude'   => '45.58929062'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'كرمانشاه',
                'latitude'    => '34.31409454',
                'longitude'   => '47.09714127'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'كرندغرب',
                'latitude'    => '34.2787056',
                'longitude'   => '46.23203659'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'كنگاور',
                'latitude'    => '34.50166702',
                'longitude'   => '47.96202087'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'كوزران',
                'latitude'    => '34.49576187',
                'longitude'   => '46.60207367'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'گهواره',
                'latitude'    => '34.34408188',
                'longitude'   => '46.41585922'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'گيلانغرب',
                'latitude'    => '34.14031982',
                'longitude'   => '45.92476654'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'ميان راهان',
                'latitude'    => '34.58388138',
                'longitude'   => '47.44466782'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'نودشه',
                'latitude'    => '35.18050385',
                'longitude'   => '46.25340652'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'نوسود',
                'latitude'    => '35.15751266',
                'longitude'   => '46.20653915'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'هرسين',
                'latitude'    => '34.27000427',
                'longitude'   => '47.58037567'
            ],
            [
                'province'    => 'كرمانشاه',
                'city'        => 'هلشي',
                'latitude'    => '34.1101532',
                'longitude'   => '47.09043121'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'باشت',
                'latitude'    => '30.3621273',
                'longitude'   => '51.15632629'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'پاتاوه',
                'latitude'    => '30.95257187',
                'longitude'   => '51.27075195'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'چرام',
                'latitude'    => '30.75547218',
                'longitude'   => '50.74684143'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'چيتاب',
                'latitude'    => '30.79491806',
                'longitude'   => '51.32473755'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'دوگنبدان',
                'latitude'    => '30.36346245',
                'longitude'   => '50.78301239'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'دهدشت',
                'latitude'    => '30.79521179',
                'longitude'   => '50.56335068'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'ديشموك',
                'latitude'    => '31.29852295',
                'longitude'   => '50.40176773'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'سوق',
                'latitude'    => '30.85565186',
                'longitude'   => '50.45983887'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'سي سخت',
                'latitude'    => '30.85756493',
                'longitude'   => '51.45872879'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'قلعه رئيسي',
                'latitude'    => '31.19034767',
                'longitude'   => '50.44401169'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'گراب سفلي',
                'latitude'    => '30.94458199',
                'longitude'   => '50.89936829'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'لنده',
                'latitude'    => '30.98266792',
                'longitude'   => '50.41968918'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'ليكك',
                'latitude'    => '30.89718437',
                'longitude'   => '50.09283447'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'مادوان',
                'latitude'    => '30.71214867',
                'longitude'   => '51.55456543'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'مارگون',
                'latitude'    => '30.99334145',
                'longitude'   => '51.07814026'
            ],
            [
                'province'    => 'كهگيلويه وبويراحمد',
                'city'        => 'ياسوج',
                'latitude'    => '30.65676308',
                'longitude'   => '51.58242416'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'انبارآلوم',
                'latitude'    => '37.13272476',
                'longitude'   => '54.61863327'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'اينچه برون',
                'latitude'    => '37.45383453',
                'longitude'   => '54.71900177'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'آزادشهر',
                'latitude'    => '37.08663177',
                'longitude'   => '55.16947937'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'آق قلا',
                'latitude'    => '37.00954056',
                'longitude'   => '54.45674515'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'بندرگز',
                'latitude'    => '36.75405884',
                'longitude'   => '53.95071411'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'تركمن',
                'latitude'    => '36.90301132',
                'longitude'   => '54.07501984'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'جلين',
                'latitude'    => '36.85464478',
                'longitude'   => '54.53674316'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'خان ببين',
                'latitude'    => '37.01268005',
                'longitude'   => '54.98781586'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'دلند',
                'latitude'    => '37.0352478',
                'longitude'   => '55.04225922'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'راميان',
                'latitude'    => '37.01845169',
                'longitude'   => '55.1420784'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'سرخنكلاته',
                'latitude'    => '36.88298035',
                'longitude'   => '54.56939316'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'سيمين شهر',
                'latitude'    => '37.01129913',
                'longitude'   => '54.23209'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'علي آباد',
                'latitude'    => '36.91240311',
                'longitude'   => '54.85770035'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'فاضل آباد',
                'latitude'    => '36.89946747',
                'longitude'   => '54.75127029'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'كردكوي',
                'latitude'    => '36.79452515',
                'longitude'   => '54.10991669'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'كلاله',
                'latitude'    => '37.38112259',
                'longitude'   => '55.4916153'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'گاليكش',
                'latitude'    => '37.27040482',
                'longitude'   => '55.43107224'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'گرگان',
                'latitude'    => '36.84519958',
                'longitude'   => '54.42879868'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'گميش تپه',
                'latitude'    => '37.07165909',
                'longitude'   => '54.07542038'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'گنبد كاووس',
                'latitude'    => '37.24781418',
                'longitude'   => '55.17573929'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'مراوه تپه',
                'latitude'    => '37.90470886',
                'longitude'   => '55.96172714'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'مينودشت',
                'latitude'    => '37.22966003',
                'longitude'   => '55.37508774'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'نگين شهر',
                'latitude'    => '37.13905716',
                'longitude'   => '55.16414642'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'نوده خاندوز',
                'latitude'    => '37.07626724',
                'longitude'   => '55.26222229'
            ],
            [
                'province'    => 'گلستان',
                'city'        => 'نوكنده',
                'latitude'    => '36.73976517',
                'longitude'   => '53.91312027'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'احمدسرگوراب',
                'latitude'    => '37.13359833',
                'longitude'   => '49.36792755'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'اسالم',
                'latitude'    => '37.73570251',
                'longitude'   => '48.94587326'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'اطاقور',
                'latitude'    => '37.11016846',
                'longitude'   => '50.11386871'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'املش',
                'latitude'    => '37.08721161',
                'longitude'   => '50.19102859'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'آستارا',
                'latitude'    => '38.38718033',
                'longitude'   => '48.86924744'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'آستانه اشرفيه',
                'latitude'    => '37.26359177',
                'longitude'   => '49.94490814'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'بازارجمعه',
                'latitude'    => '37.40681839',
                'longitude'   => '49.11826706'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'بره سر',
                'latitude'    => '36.77672577',
                'longitude'   => '49.75241089'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'بندرانزلي',
                'latitude'    => '37.46670532',
                'longitude'   => '49.46269989'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'پره سر',
                'latitude'    => '37.6034317',
                'longitude'   => '49.06938171'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'توتكابن',
                'latitude'    => '36.89390945',
                'longitude'   => '49.5256691'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'جيرنده',
                'latitude'    => '36.70132828',
                'longitude'   => '49.79056168'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'چابكسر',
                'latitude'    => '36.97463989',
                'longitude'   => '50.55472183'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'چاف وچمخاله',
                'latitude'    => '37.216465',
                'longitude'   => '50.22565842'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'چوبر',
                'latitude'    => '37.08962631',
                'longitude'   => '49.42182541'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'حويق',
                'latitude'    => '38.15422058',
                'longitude'   => '48.89034653'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'خشكبيجار',
                'latitude'    => '37.37414551',
                'longitude'   => '49.75869751'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'خمام',
                'latitude'    => '37.38215256',
                'longitude'   => '49.65184402'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'ديلمان',
                'latitude'    => '36.88354111',
                'longitude'   => '49.90714264'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رانكوه',
                'latitude'    => '37.04977417',
                'longitude'   => '50.23648453'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رحيم آباد',
                'latitude'    => '37.03316498',
                'longitude'   => '50.33705139'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رستم آباد',
                'latitude'    => '36.88407135',
                'longitude'   => '49.49157333'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رشت',
                'latitude'    => '37.28414536',
                'longitude'   => '49.59040833'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رضوانشهر ',
                'latitude'    => '37.55055618',
                'longitude'   => '49.13970184'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رودبار ',
                'latitude'    => '36.8066597',
                'longitude'   => '49.41897583'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رودبنه',
                'latitude'    => '37.2565918',
                'longitude'   => '50.00820541'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'رودسر',
                'latitude'    => '37.13264847',
                'longitude'   => '50.30025482'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'سنگر',
                'latitude'    => '37.179142',
                'longitude'   => '49.69771576'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'سياهكل',
                'latitude'    => '37.15109253',
                'longitude'   => '49.87237167'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'شفت',
                'latitude'    => '37.16381454',
                'longitude'   => '49.40309143'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'شلمان',
                'latitude'    => '37.15990067',
                'longitude'   => '50.21665192'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'صومعه سرا',
                'latitude'    => '37.30283356',
                'longitude'   => '49.32012177'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'فومن',
                'latitude'    => '37.22570038',
                'longitude'   => '49.31100082'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'كلاچاي',
                'latitude'    => '37.07468033',
                'longitude'   => '50.39940262'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'كوچصفهان',
                'latitude'    => '37.27716446',
                'longitude'   => '49.76813889'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'كومله',
                'latitude'    => '37.15062714',
                'longitude'   => '50.17618179'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'كياشهر',
                'latitude'    => '37.42233658',
                'longitude'   => '49.93053436'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'گوراب زرميخ',
                'latitude'    => '37.30015945',
                'longitude'   => '49.21863174'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'لاهيجان',
                'latitude'    => '37.20239258',
                'longitude'   => '50.01081848'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'لشت نشاء',
                'latitude'    => '37.36238861',
                'longitude'   => '49.86184311'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'لنگرود',
                'latitude'    => '37.1956749',
                'longitude'   => '50.14742661'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'لوشان',
                'latitude'    => '36.6312561',
                'longitude'   => '49.51477051'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'لولمان',
                'latitude'    => '37.25234985',
                'longitude'   => '49.82508469'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'لوندويل',
                'latitude'    => '38.30926895',
                'longitude'   => '48.86349106'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'ليسار',
                'latitude'    => '37.93748856',
                'longitude'   => '48.91623688'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'ماسال',
                'latitude'    => '37.3655014',
                'longitude'   => '49.13176727'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'ماسوله',
                'latitude'    => '37.15603638',
                'longitude'   => '48.98937988'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'مرجقل',
                'latitude'    => '37.28359985',
                'longitude'   => '49.37948608'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'منجيل',
                'latitude'    => '36.74129868',
                'longitude'   => '49.41854095'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'واجارگاه',
                'latitude'    => '37.04175186',
                'longitude'   => '50.41373825'
            ],
            [
                'province'    => 'گيلان',
                'city'        => 'هشتپر',
                'latitude'    => '37.79555893',
                'longitude'   => '48.9043808'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'ازنا',
                'latitude'    => '33.4573288',
                'longitude'   => '49.45421219'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'اشترينان',
                'latitude'    => '34.01740646',
                'longitude'   => '48.64218521'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'الشتر',
                'latitude'    => '33.86140823',
                'longitude'   => '48.25976563'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'اليگودرز',
                'latitude'    => '33.40154648',
                'longitude'   => '49.69316864'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'بروجرد',
                'latitude'    => '33.89829254',
                'longitude'   => '48.75468063'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'پلدختر',
                'latitude'    => '33.1529274',
                'longitude'   => '47.71431732'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'چالانچولان',
                'latitude'    => '33.66461563',
                'longitude'   => '48.90665817'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'چغلوندي',
                'latitude'    => '33.64917374',
                'longitude'   => '48.55954361'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'چقابل',
                'latitude'    => '33.28184891',
                'longitude'   => '47.50801849'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'خرم آباد',
                'latitude'    => '33.45696259',
                'longitude'   => '48.34891129'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'درب گنبد',
                'latitude'    => '33.69076157',
                'longitude'   => '47.14829636'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'دورود',
                'latitude'    => '33.49420166',
                'longitude'   => '49.05680847'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'زاغه',
                'latitude'    => '33.49572754',
                'longitude'   => '48.70754623'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'سپيددشت',
                'latitude'    => '33.21977615',
                'longitude'   => '48.88391113'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'سراب دوره',
                'latitude'    => '33.56311035',
                'longitude'   => '48.02005386'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'شول آباد',
                'latitude'    => '33.18558884',
                'longitude'   => '49.19091034'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'فيروز آباد',
                'latitude'    => '33.89903259',
                'longitude'   => '48.10310745'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'كوناني',
                'latitude'    => '33.40246964',
                'longitude'   => '47.32543945'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'كوهدشت',
                'latitude'    => '33.53415298',
                'longitude'   => '47.60846329'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'گراب',
                'latitude'    => '33.47413635',
                'longitude'   => '47.23699951'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'معمولان',
                'latitude'    => '33.37940979',
                'longitude'   => '47.96188354'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'مؤمن آباد',
                'latitude'    => '33.58478546',
                'longitude'   => '49.51848221'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'نور آباد',
                'latitude'    => '34.07039642',
                'longitude'   => '47.97319412'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'ويسيان',
                'latitude'    => '33.48619843',
                'longitude'   => '48.03033829'
            ],
            [
                'province'    => 'لرستان',
                'city'        => 'هفت چشمه',
                'latitude'    => '34.20405579',
                'longitude'   => '47.75966263'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'اميركلا',
                'latitude'    => '36.59878159',
                'longitude'   => '52.6627388'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'ايزدشهر',
                'latitude'    => '36.60129166',
                'longitude'   => '52.13937759'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'آلاشت',
                'latitude'    => '36.06636047',
                'longitude'   => '52.83791351'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'آمل',
                'latitude'    => '36.47745514',
                'longitude'   => '52.35583496'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'بابل',
                'latitude'    => '36.52119446',
                'longitude'   => '52.67593002'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'بابلسر',
                'latitude'    => '36.69893646',
                'longitude'   => '52.64933777'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'بلده',
                'latitude'    => '36.20122147',
                'longitude'   => '51.80610275'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'بهشهر',
                'latitude'    => '36.69554138',
                'longitude'   => '53.53464127'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'بهنمير',
                'latitude'    => '36.67020416',
                'longitude'   => '52.7622757'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'پل سفيد',
                'latitude'    => '36.11366653',
                'longitude'   => '53.0577507'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'پول',
                'latitude'    => '36.39558029',
                'longitude'   => '51.58979416'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'تنكابن',
                'latitude'    => '36.81513977',
                'longitude'   => '50.87436295'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'جويبار',
                'latitude'    => '36.64100647',
                'longitude'   => '52.89982605'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'چالوس',
                'latitude'    => '36.65398788',
                'longitude'   => '51.42446899'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'چمستان',
                'latitude'    => '36.48239899',
                'longitude'   => '52.1211319'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'خرم آباد ',
                'latitude'    => '36.7863121',
                'longitude'   => '50.86737442'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'خليل شهر',
                'latitude'    => '36.70176315',
                'longitude'   => '53.63957214'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'خوش رودپي',
                'latitude'    => '36.37145996',
                'longitude'   => '52.5656929'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'دابودشت',
                'latitude'    => '36.4806633',
                'longitude'   => '52.45203781'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'رامسر',
                'latitude'    => '36.91598892',
                'longitude'   => '50.66233444'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'رستمكلا',
                'latitude'    => '36.67808151',
                'longitude'   => '53.42604446'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'رويان',
                'latitude'    => '36.57036591',
                'longitude'   => '51.96886063'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'رينه',
                'latitude'    => '35.88198853',
                'longitude'   => '52.16975021'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'زرگر محله',
                'latitude'    => '36.51618576',
                'longitude'   => '52.5744133'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'زيرآب',
                'latitude'    => '36.17964172',
                'longitude'   => '52.97468567'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'ساري',
                'latitude'    => '36.56688309',
                'longitude'   => '53.06001663'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'سرخرود',
                'latitude'    => '36.67657471',
                'longitude'   => '52.45679092'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'سلمان شهر',
                'latitude'    => '36.6990242',
                'longitude'   => '51.19995499'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'سورك',
                'latitude'    => '36.59392166',
                'longitude'   => '53.21167374'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'شيرگاه',
                'latitude'    => '36.28995132',
                'longitude'   => '52.88095093'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'شيرود',
                'latitude'    => '36.84794998',
                'longitude'   => '50.79053116'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'عباس آباد',
                'latitude'    => '36.72123337',
                'longitude'   => '51.11597061'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'فريدونكنار',
                'latitude'    => '36.68280411',
                'longitude'   => '52.51668549'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'فريم',
                'latitude'    => '36.17418671',
                'longitude'   => '53.266922'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'قائم شهر',
                'latitude'    => '36.46435165',
                'longitude'   => '52.86508179'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'كتالم وسادات شهر',
                'latitude'    => '36.8780632',
                'longitude'   => '50.71697235'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'كلارآباد',
                'latitude'    => '36.69142914',
                'longitude'   => '51.26262665'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'كلاردشت',
                'latitude'    => '36.49369431',
                'longitude'   => '51.14495468'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'كله بست',
                'latitude'    => '36.63523483',
                'longitude'   => '52.62673569'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'كوهي خيل',
                'latitude'    => '36.68581009',
                'longitude'   => '52.90699005'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'كياسر',
                'latitude'    => '36.23791504',
                'longitude'   => '53.53940582'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'كياكلا',
                'latitude'    => '36.58187485',
                'longitude'   => '52.81481934'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'گتاب',
                'latitude'    => '36.41659546',
                'longitude'   => '52.65651321'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'گزنك',
                'latitude'    => '35.90294266',
                'longitude'   => '52.21924973'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'گلوگاه',
                'latitude'    => '36.7251358',
                'longitude'   => '53.81105042'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'محمود آباد',
                'latitude'    => '36.63143921',
                'longitude'   => '52.25236893'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'مرزن آباد',
                'latitude'    => '36.44631577',
                'longitude'   => '51.29559326'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'مرزيكلا',
                'latitude'    => '36.364048',
                'longitude'   => '52.73585129'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'نشتارود',
                'latitude'    => '36.74822998',
                'longitude'   => '51.03511047'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'نكا',
                'latitude'    => '36.65187454',
                'longitude'   => '53.29364777'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'نور',
                'latitude'    => '36.57307434',
                'longitude'   => '52.01588058'
            ],
            [
                'province'    => 'مازندران',
                'city'        => 'نوشهر',
                'latitude'    => '36.64708328',
                'longitude'   => '51.4998436'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'اراك',
                'latitude'    => '34.09344864',
                'longitude'   => '49.7220993'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'آستانه',
                'latitude'    => '33.88845825',
                'longitude'   => '49.35202026'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'آشتيان',
                'latitude'    => '34.52359009',
                'longitude'   => '50.00579453'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'پرندك',
                'latitude'    => '35.35950851',
                'longitude'   => '50.68057632'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'تفرش',
                'latitude'    => '34.68502045',
                'longitude'   => '50.02262878'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'توره',
                'latitude'    => '34.0461235',
                'longitude'   => '49.28879929'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'جاورسيان',
                'latitude'    => '34.25661087',
                'longitude'   => '49.32698822'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'خشكرود',
                'latitude'    => '35.39956665',
                'longitude'   => '50.33560562'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'خمين',
                'latitude'    => '33.64107513',
                'longitude'   => '50.08030701'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'خنداب',
                'latitude'    => '34.38344955',
                'longitude'   => '49.18894196'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'داودآباد',
                'latitude'    => '34.29349136',
                'longitude'   => '49.85509872'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'دليجان',
                'latitude'    => '33.99035645',
                'longitude'   => '50.68165588'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'رازقان',
                'latitude'    => '35.33108902',
                'longitude'   => '49.95592499'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'زاويه',
                'latitude'    => '35.37210464',
                'longitude'   => '50.57035446'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'ساروق',
                'latitude'    => '34.40793228',
                'longitude'   => '49.50768661'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'ساوه',
                'latitude'    => '35.0253334',
                'longitude'   => '50.36492157'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'سنجان',
                'latitude'    => '34.05099869',
                'longitude'   => '49.62426376'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'شازند',
                'latitude'    => '33.93370056',
                'longitude'   => '49.41087723'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'شهرجديدمهاجران',
                'latitude'    => '34.05187988',
                'longitude'   => '49.4342804'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'غرق آباد',
                'latitude'    => '35.10105896',
                'longitude'   => '49.83054352'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'فرمهين',
                'latitude'    => '34.4960289',
                'longitude'   => '49.68437195'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'قورچي باشي',
                'latitude'    => '33.67477798',
                'longitude'   => '49.87743378'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'كرهرود',
                'latitude'    => '34.06370163',
                'longitude'   => '49.65003586'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'كميجان',
                'latitude'    => '34.71912384',
                'longitude'   => '49.32233429'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'مأمونيه',
                'latitude'    => '35.31093979',
                'longitude'   => '50.4972496'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'محلات',
                'latitude'    => '33.90838242',
                'longitude'   => '50.45414352'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'ميلاجرد',
                'latitude'    => '34.62127304',
                'longitude'   => '49.20958328'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'نراق',
                'latitude'    => '34.01171112',
                'longitude'   => '50.83797073'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'نوبران',
                'latitude'    => '35.12994385',
                'longitude'   => '49.70922852'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'نيمور',
                'latitude'    => '33.88691711',
                'longitude'   => '50.57363129'
            ],
            [
                'province'    => 'مركزي',
                'city'        => 'هندودر',
                'latitude'    => '33.77946091',
                'longitude'   => '49.23086548'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'ابوموسي',
                'latitude'    => '25.88346291',
                'longitude'   => '55.02985382'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'بستك',
                'latitude'    => '27.19461441',
                'longitude'   => '54.36679459'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'بندرجاسك',
                'latitude'    => '25.65699577',
                'longitude'   => '57.80063248'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'بندرچارك',
                'latitude'    => '26.73349762',
                'longitude'   => '54.27341461'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'بندرعباس',
                'latitude'    => '27.19265175',
                'longitude'   => '56.29146576'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'بندرلنگه',
                'latitude'    => '26.54753304',
                'longitude'   => '54.88892365'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'بيكاه',
                'latitude'    => '27.3489151',
                'longitude'   => '57.1773262'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'پارسيان',
                'latitude'    => '27.20393944',
                'longitude'   => '53.04272461'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'تخت',
                'latitude'    => '27.50037193',
                'longitude'   => '56.63467789'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'جناح',
                'latitude'    => '27.01815414',
                'longitude'   => '54.28599548'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'حاجي آباد  ',
                'latitude'    => '28.30921173',
                'longitude'   => '55.90161896'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'خمير',
                'latitude'    => '26.95120621',
                'longitude'   => '55.58790207'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'درگهان',
                'latitude'    => '26.96417618',
                'longitude'   => '56.06622314'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'دهبارز',
                'latitude'    => '27.44199562',
                'longitude'   => '57.19750214'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'رويدر',
                'latitude'    => '27.46556854',
                'longitude'   => '55.41753769'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'زيارتعلي',
                'latitude'    => '27.73999596',
                'longitude'   => '57.22634125'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'سردشت بشاگرد',
                'latitude'    => '26.45518494',
                'longitude'   => '57.90121841'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'سرگز',
                'latitude'    => '27.9431839',
                'longitude'   => '56.65697861'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'سندرك',
                'latitude'    => '26.83678436',
                'longitude'   => '57.42654037'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'سوزا',
                'latitude'    => '26.7803154',
                'longitude'   => '56.06500244'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'سيريك',
                'latitude'    => '26.52111053',
                'longitude'   => '57.10928726'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'فارغان',
                'latitude'    => '28.00914574',
                'longitude'   => '56.25219727'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'فين',
                'latitude'    => '27.6335907',
                'longitude'   => '55.88210678'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'قشم',
                'latitude'    => '26.94894218',
                'longitude'   => '56.27288055'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'قلعه قاضي',
                'latitude'    => '27.44466782',
                'longitude'   => '56.54461288'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'كنگ',
                'latitude'    => '26.59617996',
                'longitude'   => '54.93657303'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'كوشكنار',
                'latitude'    => '27.2517395',
                'longitude'   => '52.86735916'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'كيش',
                'latitude'    => '26.53342438',
                'longitude'   => '53.97384644'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'گوهران',
                'latitude'    => '26.59462738',
                'longitude'   => '57.89758682'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'ميناب',
                'latitude'    => '27.09758759',
                'longitude'   => '57.07415009'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'هرمز',
                'latitude'    => '27.09114075',
                'longitude'   => '56.45739746'
            ],
            [
                'province'    => 'هرمزگان',
                'city'        => 'هشتبندي',
                'latitude'    => '27.16456795',
                'longitude'   => '57.45123672'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'ازندريان',
                'latitude'    => '34.50163269',
                'longitude'   => '48.69296265'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'اسدآباد',
                'latitude'    => '34.78535843',
                'longitude'   => '48.12166214'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'برزول',
                'latitude'    => '34.2135582',
                'longitude'   => '48.26097107'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'بهار',
                'latitude'    => '34.89070511',
                'longitude'   => '48.44088364'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'تويسركان',
                'latitude'    => '34.54904175',
                'longitude'   => '48.4481163'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'جورقان',
                'latitude'    => '34.88180542',
                'longitude'   => '48.55279922'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'جوكار',
                'latitude'    => '34.43160629',
                'longitude'   => '48.68609619'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'دمق',
                'latitude'    => '35.43703461',
                'longitude'   => '48.82205582'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'رزن',
                'latitude'    => '35.39064789',
                'longitude'   => '49.03511429'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'زنگنه',
                'latitude'    => '34.15483856',
                'longitude'   => '49.00841141'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'سامن',
                'latitude'    => '34.20925522',
                'longitude'   => '48.70354462'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'سركان',
                'latitude'    => '34.59448242',
                'longitude'   => '48.44788742'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'شيرين سو',
                'latitude'    => '35.49272537',
                'longitude'   => '48.45061111'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'صالح آباد   ',
                'latitude'    => '34.92208862',
                'longitude'   => '48.3430748'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'فامنين',
                'latitude'    => '35.11537552',
                'longitude'   => '48.97226715'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'فرسفج',
                'latitude'    => '34.4860611',
                'longitude'   => '48.28831863'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'فيروزان',
                'latitude'    => '34.36055756',
                'longitude'   => '48.11615753'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'قروه در جزين',
                'latitude'    => '35.31196594',
                'longitude'   => '49.10005188'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'قهاوند',
                'latitude'    => '34.85839462',
                'longitude'   => '49.00336075'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'كبودرآهنگ',
                'latitude'    => '35.20924377',
                'longitude'   => '48.72486877'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'گل تپه',
                'latitude'    => '35.22013855',
                'longitude'   => '48.20539474'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'گيان',
                'latitude'    => '34.17702484',
                'longitude'   => '48.24406815'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'لالجين',
                'latitude'    => '34.97359467',
                'longitude'   => '48.47839737'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'مريانج',
                'latitude'    => '34.83189392',
                'longitude'   => '48.46326828'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'ملاير',
                'latitude'    => '34.2973175',
                'longitude'   => '48.81654739'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'نهاوند',
                'latitude'    => '34.16820908',
                'longitude'   => '48.37550735'
            ],
            [
                'province'    => 'همدان',
                'city'        => 'همدان',
                'latitude'    => '34.7918129',
                'longitude'   => '48.52614594'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'ابركوه',
                'latitude'    => '31.13022423',
                'longitude'   => '53.27944946'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'احمدآباد',
                'latitude'    => '32.35710526',
                'longitude'   => '53.99137115'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'اردكان ',
                'latitude'    => '32.30692291',
                'longitude'   => '54.01856232'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'اشكذر',
                'latitude'    => '32.00024414',
                'longitude'   => '54.20687866'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'بافق',
                'latitude'    => '31.60409164',
                'longitude'   => '55.39638138'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'بفروئيه',
                'latitude'    => '32.27230835',
                'longitude'   => '53.99681091'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'بهاباد',
                'latitude'    => '31.86797905',
                'longitude'   => '56.02648926'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'تفت',
                'latitude'    => '31.74265671',
                'longitude'   => '54.20771408'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'حميديا',
                'latitude'    => '31.81936646',
                'longitude'   => '54.39827728'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'خضرآباد',
                'latitude'    => '31.86546516',
                'longitude'   => '53.95237732'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'ديهوك',
                'latitude'    => '33.28034973',
                'longitude'   => '57.51596832'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'زارچ',
                'latitude'    => '31.98426628',
                'longitude'   => '54.24261475'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'شاهديه',
                'latitude'    => '31.94301033',
                'longitude'   => '54.28015518'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'طبس',
                'latitude'    => '33.57518768',
                'longitude'   => '56.93533325'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'عشق آباد ',
                'latitude'    => '34.3663559',
                'longitude'   => '56.92831802'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'عقدا',
                'latitude'    => '32.4395256',
                'longitude'   => '53.63165665'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'مروست',
                'latitude'    => '30.46538544',
                'longitude'   => '54.21076965'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'مهردشت',
                'latitude'    => '31.02316475',
                'longitude'   => '53.35573578'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'مهريز',
                'latitude'    => '31.55741882',
                'longitude'   => '54.43781662'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'ميبد',
                'latitude'    => '32.24225235',
                'longitude'   => '54.0181427'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'ندوشن',
                'latitude'    => '32.02905273',
                'longitude'   => '53.54851532'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'نير ',
                'latitude'    => '31.48485947',
                'longitude'   => '54.12881851'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'هرات',
                'latitude'    => '30.05081558',
                'longitude'   => '54.36970139'
            ],
            [
                'province'    => 'يزد',
                'city'        => 'يزد',
                'latitude'    => '31.88352203',
                'longitude'   => '54.34774017'
            ],
        ];

        $province_ordering = 1;
        $city_ordering     = 1;

        foreach ($cities as $city) {
            $province = Province::where('name', $city['province'])->first();

            if (!$province) {
                $province = Province::create([
                    'name'      => $city['province'],
                    'ordering'  => $province_ordering++,
                ]);
            }

            City::firstOrCreate(
                [
                    'province_id' => $province->id,
                    'name'        => $city['city']
                ],
                [
                    'latitude'    => $city['latitude'],
                    'longitude'   => $city['longitude'],
                    'ordering'    => $city_ordering++
                ]
            );
        }
    }
}
