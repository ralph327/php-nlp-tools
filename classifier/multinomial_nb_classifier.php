<?php

namespace NlpTools\Classifiers;

use \NlpTools\Documents\Document;
use \NlpTools\FeatureFactories\FeatureFactory;
use \NlpTools\Models\MultinomialNBModel;

/*
 * Use a multinomia NB model to classify a document
 */
class MultinomialNBClassifier implements Classifier
{
	// The feature factory
	protected $feature_factory;
	// The NBModel
	protected $model;
	
	public function __construct(FeatureFactory $ff, MultinomialNBModel $m) {
		$this->feature_factory = $ff;
		$this->model = $m;
	}
	
	/*
	 * Compute the probability of $d belonging to each class
	 * successively and return that class that has the maximum
	 * probability.
	 * 
	 * name: classify
	 * @param $classes The classes from which to choose
	 * @param $d The document to classify
	 * @return $class The class that has the maximum probability
	 */
	public function classify(array $classes, Document $d) {
		$maxclass = current($classes);
		$maxscore = $this->getScore($maxclass,$d);
		while ($class=next($classes))
		{
			$score = $this->getScore($class,$d);
			if ($score>$maxscore)
			{
				$maxclass = $class;
				$maxscore = $score;
			}
		}
		return $maxclass;
	}
	
	/*
	 * Compute the log of the probability of the Document $d belonging
	 * to class $class. We compute the log so that we can sum over the
	 * logarithms instead of multiplying each probability.
	 * 
	 * TODO: perhaps MultinomialNBModel should have precomputed the logs
	 *       ex.: getLogPrior() and getLogCondProb()
	 * 
	 * name: getScore
	 * @param $class The class for which we are getting a score
	 * @param Document The document whose score we are getting
	 * @return float The log of the probability of $d belonging to $class
	 */
	public function getScore($class, Document $d) {
		$score = log($this->model->getPrior($class));
		$features = $this->feature_factory->getFeatureArray($class,$d);
		foreach ($features as $f)
		{
			$score += log($this->model->getCondProb($f,$class));
		}
		return $score;
	}

}

?>
