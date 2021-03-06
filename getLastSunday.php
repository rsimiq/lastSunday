<?php

/**
 * Description of dataHelper
 *
 * @author Ricardo
 */
class dataHelper 
{    
    
    /**
     * <b>dataBanco</b><br/>
     * Recebe uma data do cliente e a transforma 
     * no padrão para guardar no banco de dados
     * @param String $data = data no formato DD/MM/AAAA
     * @return String $data_banco = data no formato AAAA-MM-DD
     */
    public function dataBanco($data) 
    {
        if(!empty($data)):
            
            $d = DateTime::createFromFormat('d/m/Y', $data);
            if($d && $d->format('d/m/Y') == $data)
            {
                $data_banco = $d->format('Y-m-d');
            }
            else
            {
                $data_banco = NULL;
            }
            
        else:
            
            $data_banco = NULL;
        
        endif;
            
        return $data_banco;
    }
    
    
    /**
     * <b>dataCliente</b><br/>
     * Recebe uma data do banco de dados e a transforma 
     * no padrão para mostrar ao Cliente. 
     * @param String $data = data no formato AAAA-MM-DD
     * @return String $data_cliente = data no formato DD/MM/AAAA
     */
    public function dataCliente($data)
    {
        if(!empty($data)):
            
            $d = DateTime::createFromFormat('Y-m-d', $data);
            if($d && $d->format('Y-m-d') == $data)
            {
                $data_cliente = $d->format('d/m/Y');
            }
            else
            {
                $data_cliente = '00/00/0000';
            }
            
        else:
            
            $data_cliente = '00/00/0000';
        
        endif;
            
        
        return $data_cliente;
    }    
    
    ////////////////////////////////////////
    //         SOMA DIAS ÚTEIS
    ////////////////////////////////////////
    
    /**
     * <b>somar_dias_uteis</b>
     * Esse método soma dias úteis uma data qualquer, já descontando feriados, sábados e domingos
     * ela pode receber um vetor de feriados para ser descontados (dias que não serão contados com dia útil)
     * caso esse vetor não seja passado, a própria função cria um array com todos os feriados nacionais 
     * e faz  calculo já descontando os mesmos
     * @param Date $str_data = usar formato dd/mm/aaaa ou aaaa-mm-dd
     * @param Int $int_qtd_dias_somar = quantidade de dias úteis a ser somado
     * @param Array $feriados = vetor de feriados (opcional)
     * @return Date no formato dd/mm/aaaa
     */
    function somar_dias_uteis($str_data,$int_qtd_dias_somar = 7, $feriados = null) 
    {
        //Iniciando com uma função que retorna um array com todos os feriados nacionais do ano
        //função que retorna todos os feriados do ano
        function dias_feriados($ano = null)
        {
            if ($ano === null)
            {
              $ano = intval(date('Y'));
            }

            $pascoa     = easter_date($ano); // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
            $dia_pascoa = date('j', $pascoa);
            $mes_pascoa = date('n', $pascoa);
            $ano_pascoa = date('Y', $pascoa);

            $feriados = array(
              // Tatas Fixas dos feriados Nacionail Basileiras
              mktime(0, 0, 0, 1,  1,  ($ano + 1)), // Confraternização Universal - Lei nº 662, de 06/04/49
              mktime(0, 0, 0, 4,  21,  $ano), // Tiradentes - Lei nº 662, de 06/04/49
              mktime(0, 0, 0, 5,  1,   $ano), // Dia do Trabalhador - Lei nº 662, de 06/04/49
              mktime(0, 0, 0, 9,  7,   $ano), // Dia da Independência - Lei nº 662, de 06/04/49
              mktime(0, 0, 0, 10,  12, $ano), // N. S. Aparecida - Lei nº 6802, de 30/06/80
              mktime(0, 0, 0, 11,  2,  $ano), // Todos os santos - Lei nº 662, de 06/04/49
              mktime(0, 0, 0, 11, 15,  $ano), // Proclamação da republica - Lei nº 662, de 06/04/49
              mktime(0, 0, 0, 12, 25,  $ano), // Natal - Lei nº 662, de 06/04/49

              // These days have a date depending on easter
              mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48,  $ano_pascoa),//2ºferia Carnaval
              mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47,  $ano_pascoa),//3ºferia Carnaval	
              mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2 ,  $ano_pascoa),//6ºfeira Santa  
              mktime(0, 0, 0, $mes_pascoa, $dia_pascoa     ,  $ano_pascoa),//Pascoa
              mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60,  $ano_pascoa),//Corpus Cirist
            );

            sort($feriados);

            return $feriados;
        }

        
        if(empty($feriados))
        {
            $feriados = array();

            $ano_=date("Y");// $ano_='2010'; 
            foreach(dias_feriados($ano_) as $a)
            {
                $feriados[] =  strtotime(date("m/d/Y",$a));						 
            }
        }
        
        
        // Caso seja informado uma data do MySQL do tipo DATETIME - aaaa-mm-dd 00:00:00
        // Transforma para DATE - aaaa-mm-dd
        $str_data = substr($str_data,0,10);
        
        // Se a data estiver no formato brasileiro: dd/mm/aaaa
        // Converte-a para o padrão americano: aaaa-mm-dd
        if ( preg_match("@/@",$str_data) == 1 )
        {
            $str_data = implode("-", array_reverse(explode("/",$str_data)));
        }
        
        
        $array_data = explode('-', $str_data);
        $count_days = 0;
        $int_qtd_dias_uteis = 0;
        while ( $int_qtd_dias_uteis < $int_qtd_dias_somar )
        {
            $count_days++;
            if ( ( $dias_da_semana = gmdate('w', strtotime('+'.$count_days.' day', mktime(0, 0, 0, $array_data[1], $array_data[2], $array_data[0]))) ) != '0' && $dias_da_semana != '6' && !in_array(strtotime('+'.$count_days.' day', mktime(0, 0, 0, $array_data[1], $array_data[2], $array_data[0])),$feriados))
            {
               $int_qtd_dias_uteis++;
            }       
        }
        return gmdate('d/m/Y',strtotime('+'.$count_days.' day',strtotime($str_data)));
    }


    ////////////////////////////////////////////////////////////////////////
    //                PEGA A DATA DO DOMINGO DA SEMANA ATUAL
    ///////////////////////////////////////////////////////////////////////
    function pegaDomingoSemana()
    {
        $hoje       = date('Y-m-d');
        $arrayHoje  = explode('-',$hoje);

        $contaDias  = 0;

        //só pra poder entrar no while
        $diaDaSemana = 1;

        while($diaDaSemana > 0)
        {
            //faz a rotina de reduzir um dia para pegar quantos dias até o domingo
            $diaDaSemana = gmdate('w', strtotime('-'.$contaDias.' day',mktime(0, 0, 0, $arrayHoje[1], $arrayHoje[2], $arrayHoje[0])));
          
            if($diaDaSemana != 0)
            {
                //contador para poder reduzir os dia para pegar a data do domingo
                $contaDias++;  
            }
        }

        //usa o contaDias para reduzir o número de dias até o domingo
        $dataDomingo = gmdate('Y-m-d',strtotime('-'.$contaDias.' day',strtotime($hoje)));

        return $dataDomingo;
    }
    
}
