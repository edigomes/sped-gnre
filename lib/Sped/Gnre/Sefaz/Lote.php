<?php

/**
 * Este arquivo é parte do programa GNRE PHP
 * GNRE PHP é um software livre; você pode redistribuí-lo e/ou
 * modificá-lo dentro dos termos da Licença Pública Geral GNU como
 * publicada pela Fundação do Software Livre (FSF); na versão 2 da
 * Licença, ou (na sua opinião) qualquer versão.
 * Este programa é distribuído na esperança de que possa ser  útil,
 * mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer
 * MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a
 * Licença Pública Geral GNU para maiores detalhes.
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU
 * junto com este programa, se não, escreva para a Fundação do Software
 * Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Sped\Gnre\Sefaz;

use Sped\Gnre\Sefaz\LoteGnre;
use Sped\Gnre\Sefaz\EstadoFactory;

/**
 * Classe que armazena uma ou mais Guias (\Sped\Gnre\Sefaz\Guia) para serem
 * transmitidas. Não é possível transmitir uma simples guia em um formato unitário, para que seja transmitida
 * com sucesso a guia deve estar dentro de um lote (\Sped\Gnre\Sefaz\Lote).
 * @package     gnre
 * @subpackage  sefaz
 * @author      Matheus Marabesi <matheus.marabesi@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-howto.html GPL
 * @version     1.0.0
 */
class Lote extends LoteGnre
{

    /**
     * @var \Sped\Gnre\Sefaz\EstadoFactory
     */
    private $estadoFactory;

    /**
     * @var bool
     */
    private $ambienteDeTeste = false;

    /**
     * @return mixed
     */
    public function getEstadoFactory()
    {
        if (null === $this->estadoFactory) {
            $this->estadoFactory = new EstadoFactory();
        }

        return $this->estadoFactory;
    }

    /**
     * @param mixed $estadoFactory
     * @return Lote
     */
    public function setEstadoFactory(EstadoFactory $estadoFactory)
    {
        $this->estadoFactory = $estadoFactory;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderSoap()
    {
        $action = $this->ambienteDeTeste ?
            'http://www.testegnre.pe.gov.br/webservice/GnreRecepcaoLote' :
            'http://www.gnre.pe.gov.br/webservice/GnreRecepcaoLote';

        return array(
            'Content-Type: application/soap+xml;charset=utf-8;action="' . $action . '"',
            'SOAPAction: processar'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function soapAction()
    {
        return $this->ambienteDeTeste ?
            'https://www.testegnre.pe.gov.br/gnreWS/services/GnreLoteRecepcao' :
            'https://www.gnre.pe.gov.br/gnreWS/services/GnreLoteRecepcao';
    }

    /**
     * {@inheritdoc}
     */
    public function toXml()
    {
        $gnre = new \DOMDocument('1.0', 'UTF-8');
        $gnre->formatOutput = false;
        $gnre->preserveWhiteSpace = false;

        $loteGnre = $gnre->createElement('TLote_GNRE');
        
        $domAttribute = $gnre->createAttribute('versao');
        $domAttribute->value = "2.00";
        $loteGnre->appendChild($domAttribute);
        
        $xmlns = $this->ambienteDeTeste ?
            'http://www.testegnre.pe.gov.br' :
            'http://www.gnre.pe.gov.br';
        
        $loteXmlns = $gnre->createAttribute('xmlns');
        $loteXmlns->value = $xmlns;
        $loteGnre->appendChild($loteXmlns);
        
        $guia = $gnre->createElement('guias');
        
        foreach ($this->getGuias() as $gnreGuia) {
            $estado = $gnreGuia->c01_UfFavorecida;

            $guiaEstado = $this->getEstadoFactory()->create($estado);

            $dados = $gnre->createElement('TDadosGNRE');
            
            $domAttribute = $gnre->createAttribute('versao');
            $domAttribute->value = "2.00";
            $dados->appendChild($domAttribute);
            
            $ufFavorecida = $gnre->createElement('ufFavorecida', $estado);
            $tipoGnre = $gnre->createElement('tipoGnre', '0');
            
            $dados->appendChild($ufFavorecida);
            $dados->appendChild($tipoGnre);
            
            // Contribuinte emitente
            $contribuinteEmitente = $gnre->createElement('contribuinteEmitente');
            $identificacaoEmitente = $gnre->createElement('identificacao');
            
            if ($gnreGuia->c27_tipoIdentificacaoEmitente == parent::EMITENTE_PESSOA_JURIDICA) {
                $emitenteContribuinteDocumento = $gnre->createElement('CNPJ', $gnreGuia->c03_idContribuinteEmitente);
                //$emitenteContribuinteDocumentoIE = $gnre->createElement('IE', $gnreGuia->c17_inscricaoEstadualEmitente);
            } else {
                $emitenteContribuinteDocumento = $gnre->createElement('CPF', $gnreGuia->c03_idContribuinteEmitente);
            }
            
            $identificacaoEmitente->appendChild($emitenteContribuinteDocumento);
            //$identificacaoEmitente->appendChild($emitenteContribuinteDocumentoIE);
            
            $contribuinteEmitente->appendChild($identificacaoEmitente);
            
            $razaoSocial = $gnre->createElement('razaoSocial', $gnreGuia->c16_razaoSocialEmitente);
            $endereco = $gnre->createElement('endereco', $gnreGuia->c18_enderecoEmitente);
            $municipio = $gnre->createElement('municipio', $gnreGuia->c19_municipioEmitente);
            $uf = $gnre->createElement('uf', $gnreGuia->c20_ufEnderecoEmitente);
            
            if ($gnreGuia->c21_cepEmitente) {
                $cep = $gnre->createElement('cep', $gnreGuia->c21_cepEmitente);
            }
            if ($gnreGuia->c22_telefoneEmitente) {
                $telefone = $gnre->createElement('telefone', $gnreGuia->c22_telefoneEmitente);
            }
            
            $contribuinteEmitente->appendChild($razaoSocial);
            $contribuinteEmitente->appendChild($endereco);
            $contribuinteEmitente->appendChild($municipio);
            $contribuinteEmitente->appendChild($uf);
            $contribuinteEmitente->appendChild($cep);
            $contribuinteEmitente->appendChild($telefone);
            
            // Itens
            $itensGNRE = $gnre->createElement('itensGNRE');
            
            // Item
            $item = $gnre->createElement('item');
            
            $receita = $gnre->createElement('receita', $gnreGuia->c02_receita);
            $produto = $gnre->createElement('produto', $gnreGuia->c26_produto);
            
            $item->appendChild($receita);
            
            if ($gnreGuia->c25_detalhamentoReceita) {
                $detalhamentoReceita = $gnre->createElement('detalhamentoReceita', $gnreGuia->c25_detalhamentoReceita);
                $item->appendChild($detalhamentoReceita);
            }
            
            $documentoOrigem = $gnre->createElement('documentoOrigem', $gnreGuia->c04_docOrigem);
            $tipoOrigemAttribute = $gnre->createAttribute('tipo');
            $tipoOrigemAttribute->value = $gnreGuia->c28_tipoDocOrigem;
            $documentoOrigem->appendChild($tipoOrigemAttribute);
            $item->appendChild($documentoOrigem);
            
            if ($gnreGuia->c26_produto) {
                $item->appendChild($produto);
            }
            
            $referencia = $guiaEstado->getNodeReferencia($gnre, $gnreGuia);
            if ($referencia) {
                $item->appendChild($referencia);
            }
            
            // Vencimento
            $dataVencimento = $gnre->createElement('dataVencimento', $gnreGuia->c14_dataVencimento);
            $item->appendChild($dataVencimento);
            
            // Valor|tipo
            $valor = $gnre->createElement('valor', $gnreGuia->c06_valorPrincipal);
            $tipoValorAttribute = $gnre->createAttribute('tipo');
            
            // Case ($receita) para definir qual o tipo dependendo da receita
            $tipoValorAttribute->value = '11';
            
            $valor->appendChild($tipoValorAttribute);
            $item->appendChild($valor);
            
            if ($gnreGuia->c15_convenio) {
                $convenio = $gnre->createElement('convenio', $gnreGuia->c15_convenio);
                $item->appendChild($convenio);
            }
            
            // Contribuinte destinatário
            $contribuinteDestinatario = $gnre->createElement('contribuinteDestinatario');
            $identificacaoDestinatario = $gnre->createElement('identificacao');
            
            if ($gnreGuia->c34_tipoIdentificacaoDestinatario == parent::DESTINATARIO_PESSOA_JURIDICA) {
                $destinatarioContribuinteDocumento = $gnre->createElement('CNPJ', $gnreGuia->c35_idContribuinteDestinatario);
                $destinatarioContribuinteDocumentoIE = $gnre->createElement('IE', $gnreGuia->c36_inscricaoEstadualDestinatario);
            } else {
                $destinatarioContribuinteDocumento = $gnre->createElement('CPF', $gnreGuia->c35_idContribuinteDestinatario);
            }
            
            $identificacaoDestinatario->appendChild($destinatarioContribuinteDocumento);
            if (isset($destinatarioContribuinteDocumentoIE)) {
                $identificacaoDestinatario->appendChild($destinatarioContribuinteDocumentoIE);
            }
            
            $contribuinteDestinatario->appendChild($identificacaoDestinatario);
            
            $razaoSocialDestinatario = $gnre->createElement('razaoSocial', $gnreGuia->c37_razaoSocialDestinatario);
            $municipioDestinatario = $gnre->createElement('municipio', $gnreGuia->c38_municipioDestinatario);
            
            $contribuinteDestinatario->appendChild($razaoSocialDestinatario);
            $contribuinteDestinatario->appendChild($municipioDestinatario);
            
            $item->appendChild($contribuinteDestinatario);
            
            // Campos Extras
            $camposExtras = $guiaEstado->getNodeCamposExtras($gnre, $gnreGuia);
            if ($camposExtras != null) {
                $item->appendChild($camposExtras);
            }
            
            // Add item
            $itensGNRE->appendChild($item);
            
            $dados->appendChild($contribuinteEmitente);
            $dados->appendChild($itensGNRE);
            
            // Valor total da guia
            $valorGNRE = $gnre->createElement('valorGNRE', $gnreGuia->c10_valorTotal);
            $dados->appendChild($valorGNRE);
            
            // Data pagamento
            $dataPagamento = $gnre->createElement('dataPagamento', $gnreGuia->c33_dataPagamento);
            $dados->appendChild($dataPagamento);
            
            $guia->appendChild($dados);
            $gnre->appendChild($loteGnre);
            
            $loteGnre->appendChild($guia);
            
        }

        $this->getSoapEnvelop($gnre, $loteGnre);

        return $gnre->saveXML();
    }

    /**
     * {@inheritdoc}
     */
    public function getSoapEnvelop($gnre, $loteGnre)
    {
        $soapEnv = $gnre->createElement('soap12:Envelope');
        $soapEnv->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $soapEnv->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $soapEnv->setAttribute('xmlns:soap12', 'http://www.w3.org/2003/05/soap-envelope');

        $gnreCabecalhoSoap = $gnre->createElement('gnreCabecMsg');
        $gnreCabecalhoSoap->setAttribute('xmlns', 'http://www.gnre.pe.gov.br/wsdl/processar');
        $gnreCabecalhoSoap->appendChild($gnre->createElement('versaoDados', '2.00'));

        $soapHeader = $gnre->createElement('soap12:Header');
        $soapHeader->appendChild($gnreCabecalhoSoap);

        $soapEnv->appendChild($soapHeader);
        $gnre->appendChild($soapEnv);

        $action = $this->ambienteDeTeste ?
            'http://www.testegnre.pe.gov.br/webservice/GnreLoteRecepcao' :
            'http://www.gnre.pe.gov.br/webservice/GnreLoteRecepcao';

        $gnreDadosMsg = $gnre->createElement('gnreDadosMsg');
        $gnreDadosMsg->setAttribute('xmlns', $action);

        $gnreDadosMsg->appendChild($loteGnre);

        $soapBody = $gnre->createElement('soap12:Body');
        $soapBody->appendChild($gnreDadosMsg);

        $soapEnv->appendChild($soapBody);
    }

    /**
     * {@inheritdoc}
     */
    public function utilizarAmbienteDeTeste($ambiente = false)
    {
        $this->ambienteDeTeste = $ambiente;
    }
}
