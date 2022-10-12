<?php
/**
 * @author    Lucas Freitas <lucas@lucasfreitas.com.br
 * @license   MIT
 * @package   PagSeguro
 * @category  eCommerce, Loja Virtual, Pagamentos, PagSeguro
 */

class Pagseguro {

    /**
     * Fator para calculo de juros em parcela
     * @var array
     */
    var $fator = array(
        1  => 1,
        2  => 0.52630,
        3  => 0.356866,
        4  => 0.2721754,
        5  => 0.22142,
        6  => 0.1876,
        7  => 0.1634859,
        8  => 0.145413,
        9  => 0.131389,
        10 => 0.12018,
        11 => 0.1110269,
        12 => 0.103425,
        13 => 0.08806,
        14 => 0.08254,
        15 => 0.07777,
        16 => 0.07359,
        17 => 0.06991,
        18 => 0.06664
    );

    /**
     * Bandeiras das empresas de cartão de credito e o numero maximo de parcelas
     * @var array
     */
    var $bandeiras = array(
        1 => array(
            'nome'     => 'Visa',
            'parcelas' => 12
        ),
        2 => array(
            'nome'     => 'MasterCard',
            'parcelas' => 12
        ),
        3 => array(
            'nome'     => 'Diners',
            'parcelas' => 12
        ),
        4 => array(
            'nome'     => 'American Express',
            'parcelas' => 12
        ),
        5 => array(
            'nome'     => 'Hipercard',
            'parcelas' => 12
        ),
        6 => array(
            'nome'     => 'Aura',
            'parcelas' => 18
        )
    );

    var $parcela_minima      = 5.00;
    var $parcelas_sem_juros  = 1;

    /**
     * Converte o valor para formato numerico valido
     * @param string $valor
     * @return string
     */
    public function formatNumber($valor){

        $preco = (!empty($valor) ? $valor : "0.00");
        return number_format($preco, 2, ',', '');

    }

    /**
     * Converte o valor para um valor inteiro sem casa decimal
     * @param int $valor
     * @return string
     */
    public function formatInteiro($valor){
        $preco = (!empty($valor) ? $valor : "0.00");
        return number_format($preco, 0, ',', '');

    }

    /**
     * Realiza o calculo e cria uma array retornando o valor da parcela, total de parcela, total pago respectiva parcela.
     * @param int $valor
     * @param int $cartao
     * @return array
     */
    public function parcelamento($valor, $cartao){

        // $valor = str_replace(",", ".", $valor);

        $retorno = array();

        $retorno['cartao'] = $this->bandeiras[$cartao]['nome'];

        for ($i = 1; $i <= $this->bandeiras[$cartao]['parcelas']; $i++) {

            if ($this->parcelas_sem_juros > 1) {

                if ($i <= $this->parcelas_sem_juros) {
                    $lol = 0;
                    $fator_indice  = 1;
                    $valor_parcela = $valor * $this->fator[$fator_indice] / $i;
                    $total_pago = $valor * $this->fator[$fator_indice];
                    $taxa = $valor * $this->fator[$i];
                } else {
                    $lol = $this->parcelas_sem_juros - 1;
                    $fator_indice  = $i - $this->parcelas_sem_juros + 1 + $lol;
                    $valor_parcela = $valor * $this->fator[$fator_indice];
                    $total_pago = $valor_parcela * $i;
                    $taxa = $total_pago - $valor;
                }
                
                echo nl2br(sprintf("Fator índice: %s | $i: %s | $lol: %s", $fator_indice, $i, $lol) . PHP_EOL);
                
                if ($this->parcela_minima > $valor_parcela) {
                    break;
                }

                $retorno['valores'][$i] = array(
                    'prestacao'  => $i,
                    'valor'      => $valor_parcela,
                    'total_pago' => $total_pago,
                    'taxa'       => $taxa
                );
                
                $fator_indice++;
                
            } else {
                $valor_parcela = $valor * $this->fator[$i];
                
                if ($this->parcela_minima > $valor_parcela) {
                    break;
                }

                $retorno['valores'][$i] = array(
                    'prestacao'  => $i,
                    'valor'      => $valor_parcela,
                    'total_pago' => $valor_parcela * $i,
                    'taxa'       => ($valor_parcela * $i) - $valor
                );
                
            }
        }

        return $retorno;
    }

}

$pg = new Pagseguro();

//Primeiro campo é o valor que você deseja simular
//Segundo campo é a bandeira do cartão, lista de bandeiras abaixo:
/*
1: Visa
2: MasterCard
3: Diners
4: American Express
5: Hipercard
6: Aura
	
*/
//$retorno = $pg->parcelamento($_GET['v'], 2);
$retorno = $pg->parcelamento(1000.00, 2);

//A função Parcelamento retornará uma array() que você pode ler ela da forma que você achar melhor abaixo segue um exemplo:
echo $retorno['cartao'] . '<br/>';

foreach($retorno['valores'] as $valor ){
    echo 'Numero de Parcelas: ' . $valor['prestacao'] . ' x ';
    echo number_format($valor['valor'], 2, ',', '.') . ' | Total Pago: ';
    echo number_format($valor['total_pago'], 2, ',', '.') . ' | Taxa: ';
    echo number_format($valor['taxa'], 2, ',', '.') . '<br/>';
    
}
