<?php

namespace common\models;

use common\components\BaseActiveRecord;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "overall_files".
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $date
 * @property string  $modelClass
 * @property string  $modelId
 * @property string  $title
 * @property string  $fileName
 * @property string  $filePath
 * @property integer $inArchive
 * @property integer $sort
 */
class OverallFile extends BaseActiveRecord
{
	protected static $enableLogging = true;
	
	public $files;
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'OverallFiles';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['userId', 'date', 'inArchive', 'sort'], 'integer'],
			[['modelClass', 'modelId', 'title', 'fileName', 'filePath'], 'string', 'max' => 255],
			[['files'], 'file', 'maxFiles' => 10],
			['inArchive', 'default', 'value' => 0]
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id'         => 'ID',
			'userId'     => 'User ID',
			'date'       => 'Date',
			'modelClass' => 'Model Class',
			'modelId'    => 'Model ID',
			'title'      => 'Название (отображается пользователю)',
			'fileName'   => 'Имя файла (под этим названием скачивается файл)',
			'filePath'   => 'Папка',
			'files'      => 'Файлы',
			'sort'       => 'Сортировка'
		];
	}
	
	public function getUser()
	{
		return $this->hasOne(User::className(), ['id' => 'userId']);
	}
	
	
	public function saveFile($modelId, $modelClass)
	{
		$folder = 'overall-files';
		$dir = \Yii::getAlias('@files') . '/' . $folder;
		if (!file_exists($dir)) {
			mkdir($dir);
		}
		
		$files = UploadedFile::getInstances($this, 'files');
		
		if ($files) {
			foreach ($files as $file) {
				$item = new self();
				$item->date = time();
				$item->userId = \Yii::$app->user->identity->id;
				$newName = uniqid() . '.' . $file->extension;
				$item->filePath = $folder . '/' . $newName;
				$item->title = $file->baseName;
				$item->fileName = $file->name;
				$item->modelId = (string)$modelId;
				$item->modelClass = $modelClass;
				if (!$file->saveAs($dir . '/' . $newName)) {
					return 'error saveAs';
				}
				if (!$item->save()) {
					return $item->errors;
				}
			}
		}
		
		return true;
	}
	
	public static function getActualRegulations()
	{
		return self::find()->where(['modelClass' => DocumentSection::className()])
			->andWhere(['modelId' => DocumentSection::REGULATIONS])
			->andWhere(['inArchive' => 0])
			->orderBy(['sort' => SORT_ASC, 'date' => SORT_DESC])
			->all();
	}
	
	public function beforeValidate()
	{
		if ($this->isNewRecord && !$this->sort) {
			$this->sort = self::find()->where(['modelId' => $this->modelId, 'modelClass' => $this->modelClass])
				->max('"sort"')+1;
		}
		return parent::beforeValidate();
	}
}
