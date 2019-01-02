<?php
namespace Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Admin\Model\WfApgroup;
class GetApgroupById extends AbstractHelper
{
    public function __invoke($id){

    	$wfApgroup =  new WfApgroup();
	    return $wfApgroup->getRowById($id);
	}
}
