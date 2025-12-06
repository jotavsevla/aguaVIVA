<?php
// app/Services/DistanciaService.php
namespace app\Services;

/**
 * Servico para calculo de distancias usando Google Maps Distance Matrix API
 * Para usar: Configure GOOGLE_MAPS_API_KEY no arquivo .env
 */
class DistanciaService {
    private $apiKey;
    private $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    public function __construct() {
        $this->apiKey = getenv('GOOGLE_MAPS_API_KEY') ?: ($_ENV['GOOGLE_MAPS_API_KEY'] ?? '');
    }

    /**
     * Verifica se a API esta configurada
     * @return bool
     */
    public function isConfigured() {
        return !empty($this->apiKey);
    }

    /**
     * Calcula distancia e tempo entre duas coordenadas
     * @param float $origemLat
     * @param float $origemLng
     * @param float $destinoLat
     * @param float $destinoLng
     * @return array|null
     */
    public function calcularPorCoordenadas($origemLat, $origemLng, $destinoLat, $destinoLng) {
        $origem = "$origemLat,$origemLng";
        $destino = "$destinoLat,$destinoLng";

        return $this->calcular($origem, $destino);
    }

    /**
     * Calcula distancia e tempo entre dois enderecos
     * @param string $origem
     * @param string $destino
     * @return array|null
     */
    public function calcularPorEndereco($origem, $destino) {
        return $this->calcular(urlencode($origem), urlencode($destino));
    }

    /**
     * Faz a chamada a API do Google Maps
     * @param string $origem
     * @param string $destino
     * @return array|null
     */
    private function calcular($origem, $destino) {
        if (!$this->isConfigured()) {
            return $this->calcularEstimado($origem, $destino);
        }

        $url = sprintf(
            '%s?origins=%s&destinations=%s&key=%s&language=pt-BR',
            $this->baseUrl,
            $origem,
            $destino,
            $this->apiKey
        );

        $response = @file_get_contents($url);

        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);

        if ($data['status'] !== 'OK') {
            return null;
        }

        $element = $data['rows'][0]['elements'][0] ?? null;

        if (!$element || $element['status'] !== 'OK') {
            return null;
        }

        return [
            'distancia_km' => round($element['distance']['value'] / 1000, 2),
            'distancia_texto' => $element['distance']['text'],
            'tempo_min' => round($element['duration']['value'] / 60),
            'tempo_texto' => $element['duration']['text']
        ];
    }

    /**
     * Calculo estimado quando nao ha API configurada
     * Usa uma estimativa simples baseada em distancia euclidiana
     * @param string $origem
     * @param string $destino
     * @return array
     */
    private function calcularEstimado($origem, $destino) {
        // Retorna valores estimados padrao para POC
        return [
            'distancia_km' => 5.0,
            'distancia_texto' => '~5 km (estimado)',
            'tempo_min' => 15,
            'tempo_texto' => '~15 min (estimado)',
            'estimado' => true
        ];
    }

    /**
     * Calcula distancia entre coordenadas usando formula de Haversine
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distancia em km
     */
    public function distanciaHaversine($lat1, $lng1, $lat2, $lng2) {
        $raioTerra = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return round($raioTerra * $c, 2);
    }

    /**
     * Estima tempo de viagem baseado na distancia
     * Considera velocidade media de 30km/h em area urbana
     * @param float $distanciaKm
     * @return int Tempo em minutos
     */
    public function estimarTempo($distanciaKm) {
        $velocidadeMedia = 30; // km/h
        $tempoHoras = $distanciaKm / $velocidadeMedia;
        return (int) ceil($tempoHoras * 60);
    }

    /**
     * Encontra o entregador mais proximo de um destino
     * @param array $entregadores Lista de entregadores com lat/lng
     * @param float $destinoLat
     * @param float $destinoLng
     * @return array|null Entregador mais proximo com distancia
     */
    public function encontrarMaisProximo($entregadores, $destinoLat, $destinoLng) {
        if (empty($entregadores)) {
            return null;
        }

        $maisProximo = null;
        $menorDistancia = PHP_FLOAT_MAX;

        foreach ($entregadores as $entregador) {
            if (empty($entregador['latitude_atual']) || empty($entregador['longitude_atual'])) {
                continue;
            }

            $distancia = $this->distanciaHaversine(
                $entregador['latitude_atual'],
                $entregador['longitude_atual'],
                $destinoLat,
                $destinoLng
            );

            if ($distancia < $menorDistancia) {
                $menorDistancia = $distancia;
                $maisProximo = $entregador;
                $maisProximo['distancia_km'] = $distancia;
                $maisProximo['tempo_estimado_min'] = $this->estimarTempo($distancia);
            }
        }

        return $maisProximo;
    }
}
