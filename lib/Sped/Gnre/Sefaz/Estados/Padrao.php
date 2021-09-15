<?php

/**
 * Este arquivo Ã© parte do programa GNRE PHP
 * GNRE PHP Ã© um software livre; vocÃª pode redistribuÃ­-lo e/ou
 * modificÃ¡-lo dentro dos termos da LicenÃ§a PÃºblica Geral GNU como
 * publicada pela FundaÃ§Ã£o do Software Livre (FSF); na versÃ£o 2 da
 * LicenÃ§a, ou (na sua opiniÃ£o) qualquer versÃ£o.
 * Este programa Ã© distribuÃ­do na esperanÃ§a de que possa ser  Ãºtil,
 * mas SEM NENHUMA GARANTIA; sem uma garantia implÃ­cita de ADEQUAÃ‡ÃƒO a qualquer
 * MERCADO ou APLICAÃ‡ÃƒO EM PARTICULAR. Veja a
 * LicenÃ§a PÃºblica Geral GNU para maiores detalhes.
 * VocÃª deve ter recebido uma cÃ³pia da LicenÃ§a PÃºblica Geral GNU
 * junto com este programa, se nÃ£o, escreva para a FundaÃ§Ã£o do Software
 * Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Sped\Gnre\Sefaz\Estados;

use Sped\Gnre\Sefaz\Guia;

abstract class Padrao
{

    /**
     * @param \DOMDocument $gnre
     * @param \Sped\Gnre\Sefaz\Guia $gnreGuia
     * @return mixed
     */
    public function getNodeCamposExtras(\DOMDocument $gnre, Guia $gnreGuia)
    {
        if (is_array($gnreGuia->c39_camposExtras) && count($gnreGuia->c39_camposExtras) > 0) {
            $c39_camposExtras = $gnre->createElement('camposExtras');

            foreach ($gnreGuia->c39_camposExtras as $key => $campos) {
                $campoExtra = $gnre->createElement('campoExtra');
                $codigo = $gnre->createElement('codigo', $campos['campoExtra']['codigo']);
                //$tipo = $gnre->createElement('tipo', $campos['campoExtra']['tipo']);
                $valor = $gnre->createElement('valor', $campos['campoExtra']['valor']);

                $campoExtra->appendChild($codigo);
                //$campoExtra->appendChild($tipo);
                $campoExtra->appendChild($valor);

                $c39_camposExtras->appendChild($campoExtra);
            }

            return $c39_camposExtras;
        }

        return null;
    }

    /**
     * @param \DOMDocument $gnre
     * @param \Sped\Gnre\Sefaz\Guia $gnreGuia
     * @return \DOMElement
     */
    public function getNodeReferencia(\DOMDocument $gnre, Guia $gnreGuia)
    {
        if (!$gnreGuia->periodo && !$gnreGuia->mes && !$gnreGuia->ano && !$gnreGuia->parcela) {
            return null;
        }
        $c05 = $gnre->createElement('referencia');
        if (!is_null($gnreGuia->periodo)) {
            $periodo = $gnre->createElement('periodo', $gnreGuia->periodo);
        }
        $mes = $gnre->createElement('mes', $gnreGuia->mes);
        $ano = $gnre->createElement('ano', $gnreGuia->ano);
        if ($gnreGuia->parcela) {
            $parcela = $gnre->createElement('parcela', $gnreGuia->parcela);
        }

        if (isset($periodo)) {
            $c05->appendChild($periodo);
        }
        $c05->appendChild($mes);
        $c05->appendChild($ano);
        if (isset($parcela)) {
            $c05->appendChild($parcela);
        }

        return $c05;
    }
}
