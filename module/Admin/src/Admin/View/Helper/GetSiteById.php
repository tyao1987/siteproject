<?php
namespace Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Admin\Model\WfSite;
class GetSiteById extends AbstractHelper
{
    public function __invoke($id){

	    $site = new WfSite();
	    return $site->getRowById($id);
	}
}
