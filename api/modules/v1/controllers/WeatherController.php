<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\models\Weather;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class WeatherController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['index']]]);
    }

    #[Get(
        path: '/weather/index',
        operationId: 'weather-index',
        description: 'JSON-фвйл с погодой',
        summary: 'Погода',
        security: [['bearerAuth' => []]],
        tags: ['weather']
    )]

    #[JsonSuccess(content: [
        new Property(
            property: 'weather', type: 'array',
            items: new Items(ref: '#/components/schemas/Weather'),
        )
    ])]
    public function actionIndex(): array
    {
        $weather = Weather::findOne(['file' => 'weather_yandex_json']);

        if(empty($weather))
            $weather = new Weather();

        if(($weather->created_at+60*30) < time() && $weather->created_at != null) {
            return $this->returnSuccess([
                'weather' => $weather
            ]);
        }
        else{
            $client = new Client([
                'transport' => 'yii\httpclient\CurlTransport'
            ]);

            //токен ?
            // переменные из env ?
    //      $fh = fopen($weather->file, 'w');
            $response = $client->createRequest()
                ->setMethod('POST')
                ->addHeaders(['Authorization' => 'Basic ' . base64_encode(Yii::$app->environment->TOKEN_NAME . ":". Yii::$app->environment->TOKEN_KEY)])
                ->setUrl(Yii::$app->environment->LINK)
                ->setData(['latitude' => Yii::$app->environment->LATITUDE, 'longitude' => Yii::$app->environment->LONGITUDE])
    //               ->setOutputFile($fh)
                ->setOptions([
                    CURLOPT_CONNECTTIMEOUT => 5, // тайм-аут подключения
                     CURLOPT_TIMEOUT => 10, // тайм-аут получения данных
                ])
                ->send();

    //            file_put_contents(Yii::$app->request->hostInfo . $weather->file, $response);
                $weather->file = $response;
                $weather->created_at = time();
                $weather->save();
        }


        return $this->returnSuccess([
            'weather' =>  $weather]);


    }

}
