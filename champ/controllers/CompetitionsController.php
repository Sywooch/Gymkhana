<?php

namespace champ\controllers;

use common\models\Athlete;
use common\models\AthletesClass;
use common\models\Championship;
use common\models\City;
use common\models\Figure;
use common\models\FigureTime;
use common\models\HelpModel;
use common\models\MoscowPoint;
use common\models\Participant;
use common\models\Region;
use common\models\RegionalGroup;
use common\models\Stage;
use common\models\TmpParticipant;
use common\models\Year;
use Yii;
use yii\base\UserException;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii2fullcalendar\models\Event;

/**
 * Site controller
 */
class CompetitionsController extends BaseController
{
	const RESULTS_FIGURES = 'figure';
	const RESULTS_RUSSIA = 'russia';
	const RESULTS_REGIONAL = 'regional';
	
	public function actionSchedule()
	{
		$this->pageTitle = \Yii::t('app', 'Расписание соревнований');
		$this->description = \Yii::t('app', 'Расписание соревнований по мотоджимхане за текущий год');
		$this->keywords = 'Соревнования, календарь, календарь мотоджимханы, календарь соревнований по мотоджимхане, чемпионаты, чемпионат России';
		
		$events = [];
		$currentYear = Year::getCurrent();
		if (!$currentYear) {
			throw new UserException();
		}
		$yearIds = Year::find()->select('id')->where(['>=', 'year', $currentYear->year]);
		$championshipIds = Championship::find()->select('id')
			->andWhere(['yearId' => $yearIds])->orderBy(['dateAdded' => SORT_DESC])->asArray()->column();
		$stages = Stage::find()->where(['championshipId' => $championshipIds])
			->orderBy(['dateOfThe' => SORT_ASC, 'dateAdded' => SORT_DESC])->all();
		
		$color = '#fff';
		$dates = [];
		$notDate = [];
		/** @var Stage[] $stages */
		foreach ($stages as $stage) {
			$background = '#a9a9a9';
			if (!$stage->dateOfThe) {
				$notDate[] = $stage;
			} else {
				$month = (new \DateTime(date('01.m.Y', $stage->dateOfThe),
					new \DateTimeZone('Asia/Yekaterinburg')))
					->setTime(10, 00,
						00)->getTimestamp();
				if (!isset($dates[$month])) {
					$dates[$month] = [];
				}
				$dates[$month][] = $stage;
				if ($stage->dateOfThe + 86400 > time()) {
					$background = '#5cb85c';
				}
			}
			$event = new Event();
			$event->id = $stage->id;
			$event->title = $stage->title . ' ' . $stage->city->title;
			$event->allDay = true;
			$event->backgroundColor = $background;
			$event->color = $background;
			$event->textColor = $color;
			$event->url = Url::to(['/competitions/stage', 'id' => $stage->id]);
			$event->start = date('Y-m-d', $stage->dateOfThe);
			$events[] = $event;
		}
		
		$this->layout = 'main-with-img';
		$this->background = 'background5.png';
		
		return $this->render('schedule', ['dates' => $dates, 'notDate' => $notDate, 'events' => $events]);
	}
	
	public function actionResults($by = null)
	{
		$this->pageTitle = \Yii::t('app', 'Итоги соревнований');
		$this->description = \Yii::t('app', 'Результаты соревнований  по мотоджимхане за всё время');
		$this->keywords = 'Соревнования, чемпионаты, результаты, мотоджимхана результаты, мото джимхана, базовые фигуры';
		
		$this->layout = 'main-with-img';
		$this->background = 'background3.png';
		
		if ($by) {
			switch ($by) {
				case self::RESULTS_FIGURES:
					$figures = Figure::find();
					$pagination = new Pagination([
						'defaultPageSize' => 20,
						'totalCount'      => $figures->count(),
					]);
					$figures = $figures->orderBy(['title' => SORT_ASC])->offset($pagination->offset)->limit($pagination->limit)->all();
					$figuresArray = [];
					/** @var Figure[] $figures */
					foreach ($figures as $figure) {
						$yearIds = $figure->getResults()->select('yearId')->asArray()->column();
						$years = Year::find()->where(['id' => $yearIds])->orderBy(['year' => SORT_DESC])->all();
						$figuresArray[] = [
							'figure' => $figure,
							'years'  => $years
						];
					}
					$this->background = 'background6.png';
					
					return $this->render('figures-results', ['figuresArray' => $figuresArray, 'pagination' => $pagination]);
				case self::RESULTS_RUSSIA:
					/** @var Championship[] $championships */
					$championships = Championship::find()->where(['groupId' => Championship::GROUPS_RUSSIA])
						->orderBy(['dateAdded' => SORT_DESC])->all();
					$results = [];
					foreach ($championships as $championship) {
						$results[$championship->yearId] = [
							'year'        => $championship->year->year,
							'stages'      => $championship->stages,
							'status'      => $championship->status,
							'id'          => $championship->id,
							'showResults' => $championship->showResults
						];
					}
					if (isset($results)) {
						krsort($results);
					}
					$this->background = 'background3.png';
					
					return $this->render('russia-result', ['results' => $results]);
				case self::RESULTS_REGIONAL:
					$results = [];
					$query = Championship::find();
					$query->from(['a' => Championship::tableName(), 'b' => RegionalGroup::tableName(), 'c' => Year::tableName()]);
					$query->select('"a".*, "b".title as "groupTitle", "c"."year"');
					$query->where(['a."groupId"' => Championship::GROUPS_REGIONAL]);
					$query->andWhere(new Expression('"a"."regionGroupId" = "b"."id"'));
					$query->andWhere(new Expression('"a"."yearId" = "c"."id"'));
					$query->orderBy(['a."regionGroupId"' => SORT_ASC, 'a."dateAdded"' => SORT_DESC]);
					$championships = $query->asArray()->all();
					
					foreach ($championships as $item) {
						if (!isset($results[$item['regionGroupId']])) {
							$results[$item['regionGroupId']] = [
								'title' => $item['groupTitle'],
								'years' => []
							];
						}
						$results[$item['regionGroupId']]['years'][$item['yearId']] = [
							'year'        => $item['year'],
							'stages'      => Stage::find()->where(['championshipId' => $item['id']])
								->orderBy(['dateOfThe' => SORT_ASC, 'dateAdded' => SORT_ASC])->all(),
							'status'      => $item['status'],
							'id'          => $item['id'],
							'showResults' => $item['showResults']
						];
						
						if (isset($results)) {
							krsort($results[$item['regionGroupId']]['years']);
							ksort($results);
						}
					}
					$this->background = 'background8.png';
					
					return $this->render('regional-result', ['results' => $results]);
			}
		}
		
		return $this->render('results', ['by' => $by]);
	}
	
	public function actionStage($id, $sortBy = null, $showByClasses = false, $addOut = false)
	{
		$stage = Stage::findOne($id);
		if (!$stage) {
			throw new NotFoundHttpException(\Yii::t('app', 'Этап не найден'));
		}
		$this->pageTitle = $stage->title;
		$this->description = $stage->title;
		$this->keywords = \Yii::t('app', 'Этапы соревнования') . ': ' . $stage->title;
		$this->layout = 'full-content';
		
		$qualification = $stage->getQualificationResults();
		
		$participantsQuery = Participant::find();
		$statuses[] = Participant::STATUS_ACTIVE;
		$outCompetitionParticipants = $stage->getOutParticipants()->orderBy(['bestTime' => SORT_ASC])->all();
		if ($addOut) {
			$statuses[] = Participant::STATUS_OUT_COMPETITION;
			$participantsQuery->select(['b.*',
				'CASE WHEN "bestTime" IS NULL THEN null
				ELSE row_number() over (partition by "athleteClassId" order by "bestTime" asc)
				 END AS "tmpPlaceInAthleteClass"',
				'CASE WHEN "bestTime" IS NULL THEN null
				ELSE row_number() over (order by "bestTime" asc)
				 END AS "tmpPlace"',
				'CASE WHEN "bestTime" IS NULL THEN 1 ELSE 2 END AS time_alias']);
		} else {
			$participantsQuery->select('b.*');
		}
		$participantsQuery->from(['b' => Participant::tableName(), 'c' => AthletesClass::tableName()]);
		$participantsQuery->where(['b.stageId' => $stage->id]);
		$participantsQuery->andWhere(new Expression('"b"."athleteClassId" = "c"."id"'));
		if ($stage->participantsLimit > 0) {
			$statuses[] = Participant::STATUS_NEED_CLARIFICATION;
		}
		$participantsQuery->andWhere(['b.status' => $statuses]);
		if ($sortBy) {
			if ($addOut) {
				$participantsByJapan = $participantsQuery
					->orderBy([
						'"time_alias"' => SORT_DESC,
						'b."bestTime"' => SORT_ASC,
						'b."sort"'     => SORT_ASC,
						'b."id"'       => SORT_ASC
					])
					->all();
			} else {
				$participantsByJapan = $participantsQuery
					->orderBy([
						'b."status"'   => SORT_DESC,
						'b."bestTime"' => SORT_ASC,
						'b."sort"'     => SORT_ASC,
						'b."id"'       => SORT_ASC
					])
					->all();
			}
		} else {
			if ($addOut) {
				$participantsByJapan = $participantsQuery
					->orderBy([
						'"time_alias"' => SORT_DESC,
						'c."percent"'  => SORT_ASC,
						'b."bestTime"' => SORT_ASC,
						'"tmpPlace"'   => SORT_ASC,
						'b."sort"'     => SORT_ASC,
						'b."id"'       => SORT_ASC
					])
					->all();
			} else {
				$participantsByJapan = $participantsQuery
					->orderBy([
						'b."status"'   => SORT_DESC,
						'c."percent"'  => SORT_ASC,
						'b."bestTime"' => SORT_ASC,
						'b."sort"'     => SORT_ASC,
						'b."id"'       => SORT_ASC
					])
					->all();
			}
		}
		
		$participantsByInternalClasses = [];
		if ($stage->championship->internalClasses) {
			$participantsByInternalClasses = Participant::find();
			if ($addOut) {
				$participantsByInternalClasses->select(['*',
					'CASE WHEN "bestTime" IS NULL THEN null
				ELSE row_number() over (partition by "internalClassId" order by "bestTime" asc)
				END AS "tmpPlaceInInternalClass"',
					'CASE WHEN "bestTime" IS NULL THEN null
				ELSE row_number() over (order by "bestTime" asc)
				 END AS "tmpPlace"']);
			}
			$participantsByInternalClasses->where(['stageId' => $stage->id]);
			if ($stage->participantsLimit > 0) {
				$participantsByInternalClasses = $participantsByInternalClasses
					->andWhere(['status' => $statuses]);
			} else {
				$participantsByInternalClasses = $participantsByInternalClasses->andWhere(['status' => $statuses]);
			}
			if ($sortBy) {
				if ($addOut) {
					$participantsByInternalClasses = $participantsByInternalClasses
						->orderBy(['bestTime' => SORT_ASC, '"tmpPlace"' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC])->all();
				} else {
					$participantsByInternalClasses = $participantsByInternalClasses->orderBy(['bestTime' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC])->all();
				}
			} else {
				if ($addOut) {
					$participantsByInternalClasses = $participantsByInternalClasses
						->orderBy(['internalClassId' => SORT_ASC, 'bestTime' => SORT_ASC, '"tmpPlace"' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC])->all();
				} else {
					$participantsByInternalClasses = $participantsByInternalClasses
						->orderBy(['internalClassId' => SORT_ASC, 'bestTime' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC])->all();
				}
			}
		}
		
		$tmpParticipants = null;
		if ($stage->status == Stage::STATUS_UPCOMING || $stage->status == Stage::STATUS_START_REGISTRATION
			|| $stage->status == Stage::STATUS_END_REGISTRATION
		) {
			$tmpParticipants = TmpParticipant::find()->where(['status' => TmpParticipant::STATUS_NEW])
				->andWhere(['stageId' => $stage->id])->all();
		}
		
		$needTime = [];
		if ($stage->referenceTime) {
			/** @var AthletesClass[] $allClasses */
			$allClasses = AthletesClass::find()->orderBy(['percent' => SORT_ASC, 'title' => SORT_ASC])->all();
			/** @var AthletesClass $prev */
			$prev = null;
			$last = null;
			$prevTime = 0;
			foreach ($allClasses as $class) {
				if (!$prev || $prev->percent == $class->percent) {
					$startTime = '00:00.00';
				} else {
					$time = $prevTime + 10;
					$startTime = HelpModel::convertTimeToHuman($time);
				}
				$time = floor($stage->referenceTime * ($class->percent) / 100);
				$time = ((int)($time / 10)) * 10;
				if (round($time / $stage->referenceTime * 100, 2) >= $class->percent) {
					$time -= 10;
				}
				$prevTime = $time;
				$endTime = HelpModel::convertTimeToHuman($time);
				$needTime[$class->id] = [
					'classModel' => $class,
					'startTime'  => $startTime,
					'endTime'    => $endTime,
					'percent'    => $class->percent
				];
				$prev = $class;
				$last = $class->id;
			}
			$needTime[$last]['endTime'] = '59:59.59';
			if ($prev = AthletesClass::find()->where(['not', ['id' => $last]])->orderBy(['percent' => SORT_DESC])->one()) {
				$needTime[$last]['percent'] = '> ' . $prev->percent;
			}
		}
		
		return $this->render('stage', [
			'stage'                         => $stage,
			'participantsByJapan'           => $participantsByJapan,
			'participantsByInternalClasses' => $participantsByInternalClasses,
			'sortBy'                        => $sortBy,
			'showByClasses'                 => $showByClasses,
			'tmpParticipants'               => $tmpParticipants,
			'needTime'                      => $needTime,
			'outCompetitionParticipants'    => $outCompetitionParticipants,
			'addOut'                        => $addOut,
			'qualification'                 => $qualification
		]);
	}
	
	public function actionQualification($stageId)
	{
		$stage = Stage::findOne($stageId);
		if (!$stage) {
			throw new NotFoundHttpException(\Yii::t('app', 'Этап не найден'));
		}
		$this->pageTitle = $stage->title . ': ' . \Yii::t('app', 'Квалификационные заезды');
		$this->description = $stage->title . ': ' . \Yii::t('app', 'Квалификационные заезды');
		$this->keywords = 'Квалификационные заезды, ' . $stage->title;
		$this->layout = 'full-content';
		
		return $this->render('qualification', [
			'stage'         => $stage,
			'qualification' => $stage->getQualificationResults()
		]);
	}
	
	public function actionFigure($id, $year = null, $showAll = false)
	{
		$figure = Figure::findOne($id);
		if (!$figure) {
			throw new NotFoundHttpException(\Yii::t('app', 'Фигура не найдена'));
		}
		
		$yearModel = null;
		if ($year) {
			$yearModel = Year::findOne(['year' => $year]);
			if (!$yearModel) {
				throw new NotFoundHttpException(\Yii::t('app', 'Год не найден'));
			}
		}
		
		if ($yearModel) {
			$results = $figure->getResults();
			$results = $results->andWhere(['yearId' => $yearModel->id]);
			$results = $results
				->orderBy(['yearId' => SORT_DESC, 'resultTime' => SORT_ASC, 'date' => SORT_DESC, 'dateAdded' => SORT_DESC]);
			if (!$showAll) {
				$results = $results->limit(30);
			}
			$results = $results->all();
		} else {
			$results = FigureTime::find();
			$results->from(new Expression('Athletes, (SELECT *, rank() over (partition by "athleteId" order by "resultTime" asc, "dateAdded" asc) n 
			from "FigureTimes" where "figureId" = ' . $id . ') A'));
			$results->select('*');
			$results->where(new Expression('n=1'));
			$results->andWhere(new Expression('"Athletes"."id"="athleteId"'));
			$results->orderBy(['a."resultTime"' => SORT_ASC]);
			if (!$showAll) {
				$results = $results->limit(30);
			}
			$results = $results->all();
		}
		
		$needTime = [];
		if ($figure->useForClassesCalculate) {
			if ($figure->bestTime) {
				/** @var AthletesClass[] $allClasses */
				$allClasses = AthletesClass::find()->orderBy(['percent' => SORT_ASC, 'title' => SORT_ASC])->all();
				/** @var AthletesClass $prev */
				$prev = null;
				$last = null;
				$prevTime = 0;
				foreach ($allClasses as $class) {
					if (!$prev || $prev->percent == $class->percent) {
						$startTime = '00:00.00';
					} else {
						$time = $prevTime + 10;
						$startTime = HelpModel::convertTimeToHuman($time);
					}
					$time = floor($figure->bestTime * ($class->percent) / 100);
					$time = ((int)($time / 10)) * 10;
					$prevTime = $time;
					$endTime = HelpModel::convertTimeToHuman($time);
					$needTime[$class->id] = [
						'classModel' => $class,
						'startTime'  => $startTime,
						'endTime'    => $endTime,
						'percent'    => $class->percent
					];
					$prev = $class;
					$last = $class->id;
				}
				$needTime[$last]['endTime'] = '59:59.59';
				if ($prev = AthletesClass::find()->where(['not', ['id' => $last]])->orderBy(['percent' => SORT_DESC])->one()) {
					$needTime[$last]['percent'] = '> ' . $prev->percent;
				}
			}
		}
		
		$this->pageTitle = $figure->title;
		$this->description = \Yii::t('app', 'Результаты заездов по фигуре {figureTitle}', [
			'figureTitle' => $figure->title
		]);
		$this->keywords = 'Фигуры мотоджимханы, базовые фигуры, ' . $figure->title;
		$this->layout = 'full-content';
		
		return $this->render('figure', [
			'figure'   => $figure,
			'results'  => $results,
			'year'     => $yearModel,
			'showAll'  => $showAll,
			'needTime' => $needTime
		]);
	}
	
	public function actionProgress($figureId, $athleteId)
	{
		$figure = Figure::findOne($figureId);
		if (!$figure) {
			throw new NotFoundHttpException(\Yii::t('app', 'Фигура не найдена'));
		}
		$athlete = Athlete::findOne($athleteId);
		if (!$athlete) {
			throw new NotFoundHttpException(\Yii::t('app', 'Спортсмен не найден'));
		}
		$results = FigureTime::find()->where(['figureId' => $figureId, 'athleteId' => $athleteId])
			->orderBy(['date' => SORT_ASC, 'resultTime' => SORT_ASC])->all();
		
		$this->pageTitle = \Yii::t('app', '{athleteName}: прогресс по {figureTitle}', [
			'athleteName' => $athlete->getFullName(),
			'figureTitle' => $figure->title
		]);
		$this->description = \Yii::t('app', '{athleteName}: прогресс по {figureTitle}', [
			'athleteName' => $athlete->getFullName(),
			'figureTitle' => $figure->title
		]);
		
		$backgroundItem = rand(1, 8);
		$this->layout = 'main-with-img';
		$this->background = 'rand' . $backgroundItem . '.png';
		
		return $this->render('progress', [
			'figure'  => $figure,
			'athlete' => $athlete,
			'results' => $results
		]);
	}
	
	public function actionFigureResultsWithFilters()
	{
		\Yii::$app->response->format = Response::FORMAT_JSON;
		$response = [
			'error' => false,
			'data'  => null
		];
		$figureId = \Yii::$app->request->post('figureId');
		if (!$figureId) {
			$response['error'] = \Yii::t('app', 'Фигура не найдена');
			
			return $response;
		}
		$figure = Figure::findOne($figureId);
		if (!$figure) {
			$response['error'] = \Yii::t('app', 'Фигура не найдена');
			
			return $response;
		}
		$countryId = \Yii::$app->request->post('countryId');
		$regionIds = \Yii::$app->request->post('regionIds');
		$classIds = \Yii::$app->request->post('classIds');
		$yearId = \Yii::$app->request->post('yearId');
		$showAll = \Yii::$app->request->post('showAll');
		$date = \Yii::$app->request->post('date');
		$year = null;
		if ($countryId && !$regionIds) {
			$regionIds = Region::find()->select('id')->where(['countryId' => $countryId])->asArray()->column();
		}
		if ($yearId) {
			$year = Year::findOne($yearId);
			if (!$year) {
				$response['error'] = \Yii::t('app', 'Год не найден');
				
				return $response;
			}
		}
		$results = FigureTime::find();
		$subQuery = new Query();
		if ($year) {
			$results->from(['Athletes', 'FigureTimes']);
			$results->select('*');
			$results->andWhere(new Expression('"Athletes"."id"="FigureTimes"."athleteId"'));
			$results->andWhere(['"FigureTimes"."figureId"' => $figureId]);
			if ($regionIds) {
				$results->andWhere(['"Athletes"."regionId"' => $regionIds]);
			}
			if ($classIds) {
				$results->andWhere(['"FigureTimes"."athleteClassId"' => $classIds]);
			}
			if ($date) {
				$dateStart = (new \DateTime($date, new \DateTimeZone('Asia/Yekaterinburg')))->getTimestamp();
				$dateEnd = $dateStart + 86400;
				$results->andWhere(['>=', '"FigureTimes"."date"', $dateStart])->andWhere(['<=', '"FigureTimes"."date"', $dateEnd]);
			}
			$results->orderBy(['"FigureTimes"."resultTime"' => SORT_ASC]);
		} else {
			$subQuery->select('*, rank() over (partition by "athleteId" order by "resultTime" asc, "dateAdded" asc) n');
			$subQuery->from(FigureTime::tableName());
			if ($classIds) {
				$subQuery->where(['athleteClassId' => $classIds]);
			}
			$subQuery->andWhere(['figureId' => $figureId]);
			if ($date) {
				$dateStart = (new \DateTime($date, new \DateTimeZone('Asia/Yekaterinburg')))->getTimestamp();
				$dateEnd = $dateStart + 86400;
				$subQuery->andWhere(['>=', 'date', $dateStart])->andWhere(['<=', 'date', $dateEnd]);
			}
			$results->from(['Athletes',
				'(' . $subQuery->createCommand()->rawSql . ') A']);
			
			$results->select('*');
			$results->where(new Expression('n=1'));
			$results->andWhere(new Expression('"Athletes"."id"="athleteId"'));
			if ($regionIds) {
				$results->andWhere(['"Athletes"."regionId"' => $regionIds]);
			}
			$results->orderBy(['a."resultTime"' => SORT_ASC]);
		}
		
		if (!$showAll) {
			$results = $results->limit(30);
		}
		$results = $results->all();
		
		$response['data'] = $this->renderAjax('_figure-result', ['results' => $results, 'figure' => $figure]);
		
		return $response;
	}
	
	public function actionGetFreeNumbers($stageId)
	{
		\Yii::$app->response->format = Response::FORMAT_JSON;
		$result = [
			'success' => false,
			'error'   => false,
			'numbers' => null
		];
		$stage = Stage::findOne($stageId);
		if (!$stage) {
			$result['error'] = \Yii::t('app', 'Этап не найден');
			
			return $result;
		}
		$numbers = Championship::getFreeNumbers($stage);
		
		$result['success'] = true;
		if (!$numbers) {
			$result['numbers'] = \Yii::t('app', 'Свободных номеров нет');
		} else {
			$result['numbers'] = '<div class="row">';
			$count = ceil(count($numbers) / 3);
			foreach (array_chunk($numbers, $count) as $numbersChunk) {
				$result['numbers'] .= '<div class="col-xs-3">';
				foreach ($numbersChunk as $number) {
					$result['numbers'] .= $number . '<br>';
				}
				$result['numbers'] .= '</div>';
			}
			$result['numbers'] .= '</div>';
		}
		
		return $result;
	}
	
	public function actionAddAuthorizedRegistration()
	{
		if (\Yii::$app->user->isGuest) {
			return \Yii::t('app', 'Сначала войдите в личный кабинет');
		}
		
		$form = new Participant();
		$form->load(\Yii::$app->request->post());
		
		$stage = Stage::findOne($form->stageId);
		if (!$stage->startRegistration) {
			return \Yii::t('app', 'Регистрация на этап ещё не началась');
		}
		
		if (time() < $stage->startRegistration) {
			return \Yii::t('app', 'Регистрация на этап начнётся {startRegistration}',
				['startRegistration' => $stage->startRegistrationHuman]);
		}
		
		if ($stage->endRegistration && time() > $stage->endRegistration) {
			return \Yii::t('app', 'Регистрация на этап завершилась');
		}
		
		if ($stage->status == Stage::STATUS_CANCEL) {
			return \Yii::t('app', 'Этап отменён');
		}
		
		$championship = $stage->championship;
		$athlete = Athlete::findOne($form->athleteId);
		if ($championship->isClosed) {
			$regions = $championship->getRegionsFor(false, true);
			if ($regions && !in_array($athlete->regionId, $regions)) {
				return \Yii::t('app', 'Чемпионат закрыт для вашего региона.');
			}
		}
		
		$old = Participant::findOne(['athleteId' => $form->athleteId, 'motorcycleId' => $form->motorcycleId,
		                             'stageId'   => $form->stageId]);
		if ($old) {
			if ($old->status == Participant::STATUS_DISQUALIFICATION) {
				return \Yii::t('app', 'Вы были дисквалифицированы. Повторная регистрация невозможна');
			}
			if ($old->status == Participant::STATUS_CANCEL_ADMINISTRATION) {
				return \Yii::t('app', 'Ваша заявка отклонена. Чтобы узнать подробности, свяжитесь с организатором этапа');
			}
			if ($old->status == Participant::STATUS_CANCEL_ATHLETE) {
				if ($old->number != $form->number) {
					if ($form->number) {
						$freeNumbers = Championship::getFreeNumbers($stage, $form->athleteId);
						if (!in_array($form->number, $freeNumbers)) {
							return \Yii::t('app', 'Номер занят. Выберите другой или оставьте поле пустым.');
						}
						$old->number = $form->number;
					} elseif ($athlete->number && $championship->regionId && $athlete->regionId == $championship->regionId) {
						$old->number = $athlete->number;
					}
				}
				$old->status = Participant::STATUS_NEED_CLARIFICATION;
				if ($old->save()) {
					return true;
				}
				
				return var_dump($old->errors);
			}
			
			return \Yii::t('app', 'Вы уже зарегистрированы на этот этап на этом мотоцикле.');
		}
		
		if (\Yii::$app->mutex->acquire('setNumber' . $stage->id, 10)) {
			if ($form->number) {
				$freeNumbers = Championship::getFreeNumbers($stage, $form->athleteId);
				if (!in_array($form->number, $freeNumbers)) {
					return \Yii::t('app', 'Номер занят. Выберите другой или оставьте поле пустым.');
				}
			} elseif ($athlete->number && $championship->regionId && $athlete->city->regionId == $championship->regionId) {
				$form->number = $athlete->number;
			} else {
				$freeNumbers = Championship::getFreeNumbers($stage);
				if ($freeNumbers) {
					//$form->number = $freeNumbers[0]; //присвоение случайного номера
				}
			}
			\Yii::$app->mutex->release('setNumber' . $stage->id);
			if ($form->save()) {
				\Yii::$app->mutex->release('setNumber' . $stage->id);
				$this->sendConfirmEmail($championship, $stage, $form);
				
				return true;
			} else {
				\Yii::$app->mutex->release('setNumber' . $stage->id);
				
				return var_dump($form->errors);
			}
		}
		\Yii::$app->mutex->release('setNumber' . $stage->id);
		
		return \Yii::t('app', 'Внутренняя ошибка. Пожалуйста, попробуйте позже.');
	}
	
	public function actionAddUnauthorizedRegistration()
	{
		$form = new TmpParticipant();
		$form->load(\Yii::$app->request->post());
		
		$stage = Stage::findOne($form->stageId);
		if (!$stage->startRegistration) {
			return \Yii::t('app', 'Регистрация на этап ещё не началась');
		}
		
		if (time() < $stage->startRegistration) {
			return \Yii::t('app', 'Регистрация на этап начнётся {startRegistration}',
				['startRegistration' => $stage->startRegistrationHuman]);
		}
		
		if ($stage->endRegistration && time() > $stage->endRegistration) {
			return \Yii::t('app', 'Регистрация на этап завершилась.');
		}
		
		if ($stage->status == Stage::STATUS_CANCEL) {
			return \Yii::t('app', 'Этап отменён');
		}
		
		if (!$form->city && !$form->cityId) {
			return \Yii::t('app', 'Необходимо указать город');
		}
		
		$championship = $stage->championship;
		if ($championship->isClosed) {
			$regionId = null;
			if ($form->cityId) {
				$city = City::findOne($form->cityId);
				$regionId = $city->regionId;
			}
			if (!$regionId) {
				return \Yii::t('app', 'Чемпионат закрыт для вашего города');
			}
			$regions = $championship->getRegionsFor(false, true);
			if ($regions && !in_array($regionId, $regions)) {
				return \Yii::t('app', 'Чемпионат закрыт для вашего города');
			}
		}
		
		if (\Yii::$app->mutex->acquire('setNumber' . $stage->id, 10)) {
			if ($form->number) {
				$freeNumbers = Championship::getFreeNumbers($stage);
				if (!in_array($form->number, $freeNumbers)) {
					return \Yii::t('app', 'Номер занят. Выберите другой или оставьте поле пустым.');
				}
			} else {
				$freeNumbers = Championship::getFreeNumbers($stage);
				if ($freeNumbers) {
					//$form->number = $freeNumbers[0]; //присвоение случайного номера
				}
			}
			if (!$form->validate()) {
				return \Yii::t('app', 'Необходимо указать имя, фамилию, город, email, а так же марку и модель мотоцикла.');
			}
			if ($form->save(false)) {
				\Yii::$app->mutex->release('setNumber' . $stage->id);
				$this->sendConfirmEmail($championship, $stage, null, $form);
				
				return true;
			} else {
				\Yii::$app->mutex->release('setNumber' . $stage->id);
				
				return var_dump($form->errors);
			}
		}
		\Yii::$app->mutex->release('setNumber' . $stage->id);
		
		return \Yii::t('app', 'Внутренняя ошибка. Пожалуйста, попробуйте позже.');
	}
	
	private function sendConfirmEmail(Championship $championship, Stage $stage, Participant $participant = null, TmpParticipant $tmpParticipant = null)
	{
		$email = null;
		if ($tmpParticipant && $tmpParticipant->email) {
			$email = $tmpParticipant->email;
		} elseif ($participant) {
			$athlete = $participant->athlete;
			$email = $athlete->email;
		}
		if ($stage->participantsLimit > 0 && $email && mb_stripos($email, '@', null, 'UTF-8')) {
			if (YII_ENV != 'dev') {
				\Yii::$app->mailer->compose('confirm-request', [
					'championship'   => $championship,
					'stage'          => $stage,
					'tmpParticipant' => $tmpParticipant,
					'participant'    => $participant
				])
					->setTo($email)
					->setFrom(['support@gymkhana-cup.ru' => 'GymkhanaCup'])
					->setSubject('gymkhana-cup:' . \Yii::t('app', 'предварительная регистрация на этап'))
					->send();
			}
		}
		
		return true;
	}
	
	public function actionChampionshipResult($championshipId, $showAll = null)
	{
		$championship = Championship::findOne($championshipId);
		if (!$championship) {
			throw new NotFoundHttpException(\Yii::t('app', 'Чемпионат не найден'));
		}
		if (!$championship->showResults) {
			throw new UserException(\Yii::t('app', 'Для данного чемпионата не ведётся подсчёт итогов'));
		}
		$this->pageTitle = \Yii::t('app', '{champTitle}: итоги', [
			'champTitle' => $championship->title
		]);
		$this->description = \Yii::t('app', 'Итоги соревнования: {champTitle}', [
			'champTitle' => $championship->title
		]);
		
		$stages = $championship->stages;
		$results = $championship->getResults($showAll);
		
		return $this->render('championship-results', [
			'championship' => $championship,
			'results'      => $results,
			'stages'       => $stages,
			'showAll'      => $showAll
		]);
	}
	
	public function actionChampionship($id)
	{
		$championship = Championship::findOne($id);
		if (!$championship) {
			throw new NotFoundHttpException(\Yii::t('app', 'Чемпионат не найден'));
		}
		$this->pageTitle = $championship->title;
		$this->description = \Yii::t('app', 'Информация о чемпионате: {champTitle}', [
			'champTitle' => $championship->title
		]);
		$this->layout = 'full-content';
		
		return $this->render('championship', ['championship' => $championship]);
	}
	
	public function actionMoscowScheme()
	{
		$this->pageTitle = \Yii::t('app', 'Московская схема начисления баллов');
		$this->description = \Yii::t('app', 'Схема начисления баллов за этап в Москве');
		
		$points = (new Query())->select('*')
			->from(['a' => MoscowPoint::tableName(), 'b' => AthletesClass::tableName()])
			->where(new Expression('"a"."class" = "b"."id"'))
			->orderBy(['b."percent"' => SORT_ASC, 'b."title"' => SORT_ASC, 'a."place"' => SORT_ASC])->all();
		$items = ArrayHelper::map($points,
			'place', 'point', 'title');
		
		return $this->render('moscow-scheme', ['items' => $items]);
	}
}
