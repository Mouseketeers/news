<?php
class NewsPage extends Page {
	static $db = array(
		'FromDate' => 'Date',
		'ToDate' => 'Date',
		'Abstract' => 'Text'
	);
	static $has_one = array(
		'Image' => 'Image'
	);
	static $icon  = 'news/images/news';
	static $default_parent = 'NewsSection';
	static $allowed_children = 'none';
	static $can_be_root = false;
	static $default_sort = 'FromDate DESC';
	static $defaults = array(
		'ShowInMenus' => false,
		'FromDate' => 'now'
	);
	static $default_upload_folder = 'News';
	static $api_access = array (
		'view' => array(
			'ID',
			'Title',
			'Content',
			'Abstract',
			'ResizedImage',
			'FromDate',
			'ToDate'
		)
	);
	function canView($member = null) {
		if(Permission::checkMember($member, 'ADMIN')) {
			return true;
		}
		$now = strtotime('now');
		if($this->FromDate && strtotime($this->FromDate) >= $now || $this->ToDate && strtotime($this->ToDate) <= $now) {
			return false;
		}
		return true;
	}
	function getCMSFields() {
		
		$from_date_field = new DateField('FromDate', _t('NewsPage.FROM','From'));
		$from_date_field->setConfig('showcalendar',true);
		
		$to_date_field = new DateField('ToDate', _t('NewsPage.TO','To'));
		$to_date_field->setConfig('showcalendar',true);
		
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Content.Main',new TextareaField('Abstract','Abstract'),'Content');
		$fields->addFieldToTab(
			$tab = 'Root.Content.Main',
			$from_date_field,
			$place_before = 'Content'
		);
		$fields->addFieldToTab(
			$tab = 'Root.Content.Main',
			$to_date_field,
			$place_before = 'Content'
		);
		//$fields->addFieldToTab('Root.Content.Image',new ImageUploadField('Image','Image'));
		return $fields;
	}
	public function DateAndTitle() {
		return $this->FromDate . ' - ' . $this->Title;
	}
	public function getRestfulSearchContext() {
		if (!class_exists('DateFilter')) return $this->getDefaultSearchContext();
		return new SearchContext(
			$this->class,
			null,
			array(
				'FromDate' => new DateFilter('FromDate'),
				'ToDate' => new DateFilter('ToDate')
			)
		);
	}
}
class NewsPage_Controller extends Page_Controller {
	public function OtherNews($limit='') {
		$filter = '`NewsPage`.`ID` <> '.$this->ID
			.' AND ParentID = '.$this->ParentID
			.' AND (FromDate IS NULL OR FromDate <= NOW()) AND (ToDate IS NULL OR ToDate >= NOW())';
		$sortorder = 'FromDate DESC';
		return DataObject::get('NewsPage', $filter, $sortorder, '', $limit);
	}
}
?>