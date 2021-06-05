<?php
class BlogInfo
{
	/**
	 * ブログ更新をツイートする機能
	 */

	public $common;				// 共通処理オブジェクト
	public $blogFile;			// ブログファイル名(blog.txt)

	/**
	 * コンストラクタ
	 *
	 * @param common 共通処理オブジェクト
	 */
	function __construct($common) {
		$this->common = $common;
		$this->blogFile = $this->common->getTxtDir()."blog.txt";
		$this->common->debug($this->blogFile);
	}

	/**
	 * ブログ情報を取得
	 * 更新無ければ空文字列を返却する。
	 *
	 * @return ツイートするメッセージ
	 */
	public function getBlog() {
		$message = "";

		// ブログURL取得
		$contents = @file_get_contents($this->blogFile);
		// URLからテキスト取得
		$urltxt = $this->common->getBlogURL();
		$this->common->debug("urltxt: ".$urltxt);

		// 最新のURL取得
		$newUrl = $this->common->getNewUrlAmeba($urltxt);

		// メンテナンス中の場合、値取得失敗する
		if ($newUrl == "" || $newUrl == " ") {
			echo "mentenance.\n";
			return "";
		}
		
		$this->common->debug($newUrl);
		$this->common->debug("contents:".$contents);

		// 等しい場合 = 0
		if (strcmp($contents, $newUrl) == 0) {
			$this->common->debug("not Change.");
			return "";
		}
		// 変更がある場合
		else{
			// チャタリング除去
			if(strcmp($this->common->getNewUrl_ameba($urltxt), $newUrl) == 0) {
				// 変更有のため、処理
				$blogTitle = $this->common->getNewTitle_ameba($urltxt);
				// メンテナンス中の場合、値取得失敗する
				if ($blogTitle == "" || $blogTitle == " ") {
					echo "mentenance.\n";
					return "";
				}
				
				$this->common->debug($blogTitle);
				
				if (strlen($blogTitle) > 60) {
					$blogTitle = substr($blogTitle,0,60);
				}

				// ファイル出力
				file_put_contents($this->blogFile, $newUrl);

				// アスキー文字を整形する
				$blogTitle = $this->common->changeAscii($blogTitle);

				// ※URL以外：46文字 URL：60文字（おおよそ実際は57か8）※
				IF(mb_strlen($blogTitle, "UTF-8") < 30) {
					// 通常版
					$message = "【石田晴香ブログ】 はるきゃんのブログが更新されました。『 ".$blogTitle." 』 ".$newUrl."　#石田晴香 #はるきゃん";
				}
				ELSE {
					// 簡素版
					$message = "【石田晴香ブログ】 はるきゃんのブログが更新されました。（タイトル略） ".$newUrl;
				}
			}
		}

		return $message;
    }
}
?>
