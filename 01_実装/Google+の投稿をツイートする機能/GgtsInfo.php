<?php
require_once("ErrorCheck.php");
require_once("MemberCreater.php");

class GgtsInfo
{
    /**
     * Google+の投稿をツイートする機能
     */
     
	public $common;					// 共通処理のオブジェクト
	public $memberInfo;				// メンバー情報

	// 最新記事
	public $ggtsFile;				// Google+情報保存用ファイル
	public $comentCounterFile;		// カウンタ保持ファイル
	public $hokamenFile;			// 他メンバー情報保存用ファイル
	
	// 過去記事
	public $ggtsFileOld;			// Google+情報保存用ファイル
	public $comentCounterFileOld;	// カウンタ保持ファイル
	public $hokamenFileOld;			// 他メンバー情報保存用ファイル

	public $saishinFlg;				// 最新記事OR過去記事フラグ


    /**
     * コンストラクタ
     *
     * @param common 共通処理オブジェクト
     * @param flg 最新記事：0,過去記事：1
     */
	function __construct($common, $flg) {
		$this->common     = $common;
		$this->saishinFlg = $flg;

		// 最新記事
		IF($this->saishinFlg) {
			$this->ggtsFile    = $this->common->getTxtDir()."can.txt";
			$this->comentCounterFile = $this->common->getTxtDir()."coment_counter.txt";
			$this->hokamenFile = $this->common->getTxtDir()."hokamen.txt";
			
			$this->ggtsFileOld    = $this->common->getTxtDir()."old_can.txt";
			$this->comentCounterFileOld = $this->common->getTxtDir()."old_coment_counter.txt";
			$this->hokamenFileOld = $this->common->getTxtDir()."old_hokamen.txt";
		}
		// 過去記事
		ELSE {
			$this->ggtsFile    = $this->common->getTxtDir()."old_can.txt";
			$this->comentCounterFile = $this->common->getTxtDir()."old_coment_counter.txt";
			$this->hokamenFile = $this->common->getTxtDir()."old_hokamen.txt";
		}
	}

    /**
     * Google+情報を取得
     * 更新無ければ空文字列を返却する。
     *
     * @return ツイートするメッセージ
     */
	public function getGgts() {
		$message = "";

		$contents = @file_get_contents($this->ggtsFile);
		// 最新の記事URLを取得する
		$memID = $this->common->getMemId();
		$newKijiURL = $this->common->getNewUrlGgts($memID);

		IF (strcmp($contents, $newKijiURL) == 0) {
			$this->common->debug("not change.");
			return "";
		}
		// 変更あった場合
		ELSE {
			// チャタリング除去
			if(strcmp($this->common->getNewUrl_ggts($memID), $newKijiURL) == 0) {
				// メンテナンス中の場合、値取得失敗する
				if ($newKijiURL == "" || $newKijiURL == " ") {
					echo "mentenance.\n";
					return "";
				}

				// Google+記事取得
				$hikakuKiji = $this->common->getNewKiji_ggts($newKijiURL);

				// ツイッター連動チェック
				IF($this->common->checkTwitterRendo($hikakuKiji)) {
					if (mb_strlen($hikakuKiji, "UTF-8") > 55) {
						$hikakuKiji = mb_substr($hikakuKiji,0,55,"UTF-8")."...";
					}
					$message = "(ツイ連)【Google+】 ".$hikakuKiji." ".$newKijiURL;
				}
				ELSE {
					if (mb_strlen($hikakuKiji, "UTF-8") > 55) {
						$hikakuKiji = mb_substr($hikakuKiji,0,55,"UTF-8")."...";
					}
					// 出力メッセージ
					$message = "【Google+限定投稿】 ".$hikakuKiji." ".$newKijiURL;
				}

				// 処理実行した記事をOLDファイルに出力する。
				$newGgtsFile = @file_get_contents($this->ggtsFile);
				$newComentCounterFile = @file_get_contents($this->comentCounterFile);
				$newHokamenFile = @file_get_contents($this->hokamenFile);
				file_put_contents($this->ggtsFileOld, $newGgtsFile);
				file_put_contents($this->comentCounterFileOld, $newComentCounterFile);
				file_put_contents($this->hokamenFileOld, $newHokamenFile);
				
				file_put_contents($this->ggtsFile, $newKijiURL);
				file_put_contents($this->comentCounterFile, "0");
				file_put_contents($this->hokamenFile, "header");
			}
		}

		return $message;
    }

    /**
     * メンバ情報配列を取得する
     *
     * @return メンバ情報配列
     */
    private function getMemberArr() {
		return $this->memberInfo->getMemNameArr();
    }
}
?>