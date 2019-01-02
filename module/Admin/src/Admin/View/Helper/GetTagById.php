<?php
namespace Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Admin\Model\WfTag;
class GetTagById extends AbstractHelper
{
    public function __invoke($id){

	    $tag = new WfTag();
	    return $tag->getRowById($id);
	}
}
