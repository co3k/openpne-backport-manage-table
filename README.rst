===============================
OpenPNE 3 Backport Manage Table
===============================

なんだこれ
==========

OpenPNE 3 のバックポートチケットを管理するためのやつ。

使い方
======

index.php のソースコードを読んで、必要に応じて書き換えて、サーバに設置するだけ。

ライセンス
==========

MIT License

サードパーティライブラリ
========================

* jQuery <http://jquery.com/>
* jQuery Tablesorter Plugin <http://tablesorter.com/>
* Goutte <https://github.com/fabpot/Goutte>

誰がやった
==========

Kousuke Ebihara <ebihara@php.net>
    http://co3k.org/

注意事項
========

* PHP 5.3 じゃないと動かないですよ
* <del>redmine.openpne.jp にガンガン API リクエストしまくるので refresh リンクとか押しまくらないでくださいお願いします。あと信頼できない第三者に refresh されまくって redmine.openpne.jp を DoS っぽくしちゃって責任追及されるとかあると思うので設置するところに認証とかかけておいたほうがいいですよ</del>
* Web からキャッシュをリフレッシュする方式は廃止しました。なるはやでバッチで更新する手段に切り替える予定
* 3.4 とか 3.0 とかのカラムをソートしようとしてもなぜかうまくいかないのでフィルタで代用した方がいい
* これは早めに調整するけれどもバックポートチケット検出がやや不確実だと思う
