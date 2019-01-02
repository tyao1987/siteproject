<?php
namespace Admin\View\Helper;

use Admin\Model\WfAc;
use Zend\View\Helper\AbstractHelper;
class GetAcById extends AbstractHelper
{
    public function __invoke($id){

	    $ac = new WfAc();
	    return $ac->getRowById($id);
	}
}
