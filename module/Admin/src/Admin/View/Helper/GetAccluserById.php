<?php
namespace Admin\View\Helper;

use Admin\Model\WfAccluster;
use Zend\View\Helper\AbstractHelper;
class GetAccluserById extends AbstractHelper
{
    public function __invoke($id){

	    $accluser = new WfAccluster();
	    return $accluser->getRowById($id);
	}
}
