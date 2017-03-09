<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Notice;
use yii\db\ActiveRecord;

/**
 * NoticeSearch represents the model behind the search form about `common\models\Notice`.
 */
class NoticeSearch extends Notice
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'athleteId', 'status', 'dateAdded'], 'integer'],
			[['text', 'link'], 'safe'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}
	
	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$query = Notice::find();
		
		// add conditions that should always apply here
		
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);
		
		$this->load($params);
		
		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}
		
		// grid filtering conditions
		$query->andFilterWhere([
			'id'        => $this->id,
			'athleteId' => $this->athleteId,
			'status'    => $this->status,
			'dateAdded' => $this->dateAdded,
		]);
		
		$query->andFilterWhere(['like', 'text', $this->text])
			->andFilterWhere(['like', 'link', $this->link]);
		
		return $dataProvider;
	}
	
	public function beforeValidate()
	{
		return ActiveRecord::beforeValidate();
	}
}
