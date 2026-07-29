# 学習ノート機能 設計書

作成日: 2026-07-29

## 概要

英語の文法など学んだことを、画像や文章でメモできる機能。ブログのように1トピック=1メモ（例：「現在分詞とは？」「過去進行形とは？」）として複数のメモを作成・管理できる。

## 決定事項

- **エディタ形式**: シンプル入力（タイトル + 本文テキストエリア + 画像アップロード複数枚）。Markdown・リッチテキストは採用しない。
- **整理方法**: ユーザーが自分でカテゴリを作成して分類（例：文法、イディオム）。一覧でカテゴリ絞り込みができる。
- **公開範囲**: 自分専用。ログインした本人のメモだけ表示・編集できる。
- **プラン制限**: なし。全ユーザー無制限で提供（Plan/Subscription との連携は将来必要になったら追加）。

## データモデル

3テーブル構成。既存の Word / Japanese と同様に user_id で所有者に紐付ける。

### notes
| カラム | 型 | 備考 |
|---|---|---|
| id | bigint | PK |
| user_id | bigint | FK → users、cascade delete |
| note_category_id | bigint nullable | FK → note_categories、カテゴリ削除時は null（今回カテゴリ削除UIはないが制約として nullOnDelete） |
| title | string | 必須（例：現在分詞とは？） |
| body | text nullable | 本文テキスト |
| timestamps | | |

### note_categories
| カラム | 型 | 備考 |
|---|---|---|
| id | bigint | PK |
| user_id | bigint | FK → users、cascade delete |
| name | string | 同一ユーザー内でユニーク（unique(user_id, name)） |
| timestamps | | |

### note_images
| カラム | 型 | 備考 |
|---|---|---|
| id | bigint | PK |
| note_id | bigint | FK → notes、cascade delete |
| path | string | storage 上の相対パス |
| timestamps | | |

## 画面・ルーティング

すべて `auth` ミドルウェア配下。自分のデータのみ操作可。

| メソッド | パス | 名前 | 内容 |
|---|---|---|---|
| GET | /notes | notes.index | 一覧。カード形式（タイトル・カテゴリ・更新日）、新着順。カテゴリで絞り込み（クエリパラメータ） |
| GET | /notes/create | notes.create | 作成フォーム |
| POST | /notes | notes.store | 保存 |
| GET | /notes/{note} | notes.show | 詳細。本文の下に画像を縦に並べて表示 |
| GET | /notes/{note}/edit | notes.edit | 編集フォーム。既存画像の個別削除チェック + 追加アップロード |
| PATCH | /notes/{note} | notes.update | 更新 |
| DELETE | /notes/{note} | notes.destroy | 削除（紐付く画像ファイルも削除） |

コントローラは `NoteController` を新規作成（リソースコントローラ形式）。

### カテゴリの扱い

- 作成・編集フォームで「既存カテゴリから選択」または「新しいカテゴリ名をその場で入力」できる。新規名が入力された場合はカテゴリを作成（同名が既にあればそれを使用）。
- カテゴリの削除・リネーム機能は今回のスコープ外。

## 画像の扱い

- 保存先: `storage/app/public/note-images/{user_id}/`（公開は `php artisan storage:link` 経由）
- バリデーション: jpg / jpeg / png / webp / gif、1枚 5MB まで、1メモ最大10枚（既存画像 + 新規追加の合計で10枚まで）
- メモ削除・画像個別削除時は storage 上のファイルも削除する

## 認可・エラー処理

- 他人のメモ・画像へのアクセスは 404（コントローラで user_id を確認、または NotePolicy）
- 入力エラーは既存機能と同じく Laravel バリデーション + Blade でのエラー表示

## デザイン

- CLAUDE.md のガイドラインに従う：黄（#ffeb54）・黒・白の3色のみ、Zen Kaku Gothic New、ミニマルで余白を活かす
- 既存の words 画面・レイアウト（layouts 配下）のトーンとナビゲーションに合わせ、ヘッダーに「学習ノート」リンクを追加

## テスト

Feature テストで以下を担保する：

- メモの CRUD（作成・一覧・詳細・更新・削除）
- 認可：未ログインはリダイレクト、他人のメモは 404
- カテゴリ：新規作成、既存選択、同名入力時の再利用、絞り込み
- 画像：アップロード（Storage::fake）、上限枚数・形式・サイズのバリデーション、削除時のファイル削除

## スコープ外（将来検討）

- カテゴリの削除・リネーム UI
- キーワード検索
- Markdown 対応
- 他ユーザーへの公開
- プランによる上限設定
