<?php
namespace PoraboteApp\Documents;

class DocumentsFactory
{
	public function newDocument($type)
	{
		switch($type) {
			case "CommercialProposals" : return new CommercialProposals;
			default : throw new \Exception('Тип документа не поддерживается');
		}
	}
}
	
?>