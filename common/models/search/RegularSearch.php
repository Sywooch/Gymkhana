<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Regular;
use yii\db\ActiveRecord;

/**
 * RegularSearch represents the model behind the search form about `common\models\Regular`.
 */
class RegularSearch extends Regular
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'sort'], 'integer'],
			[['text'], 'safe'],
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
		$query = Regular::find();

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
			'id'   => $this->id,
			'sort' => $this->sort,
		]);

		$query->andFilterWhere(['like', 'text', $this->text]);

		return $dataProvider;
	}
	
	public function beforeValidate()
	{
		return ActiveRecord::beforeValidate();
	}
}
