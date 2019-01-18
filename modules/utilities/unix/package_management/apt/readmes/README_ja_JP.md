# apt

#### 目次


1. [説明 - モジュールの機能とその有益性](#module-description)
1. [セットアップ - apt導入の基本](#setup)
    * [aptが影響を与えるもの](#what-apt-affects)
    * [aptの使用を開始する](#beginning-with-apt)
1. [使用 - 設定オプションと追加機能](#usage)
    * [GPGキーの追加](#add-gpg-keys)
    * [バックポートの優先度を上げる](#prioritize-backports)
    * [パッケージリストの更新](#update-the-list-of-packages)
    * [特定のリリースのピン止め](#pin-a-specific-release) 
    * [PPA (Personal Package Archive)レポジトリの追加](#add-a-personal-package-archive-repository)
    * [HieraからのAptの構成](#configure-apt-from-hiera)
    * [デフォルトのsources.listファイルの置き換え](#replace-the-default-sourceslist-file)
1. [参考 - モジュールの機能と動作について](#reference)
1. [制約 - OS互換性など](#limitations)
1. [開発 - モジュール貢献についてのガイド](#development)

## モジュールの概要

aptモジュールを導入すると、Puppetを使用してAPT (Advanced Package Tool)のソース、キー、その他の構成オプションを管理できます。

APTとは、Debian、Ubuntu、およびその他いくつかのオペレーティングシステムで利用可能なパッケージマネージャです。aptモジュールは、APTのパッケージ管理を自動化するのに役立つ一連のクラス、定義型、およびfactsを提供します。

**注意**: このモジュールが実行中のDebian/Ubuntu (もしくは派生OS)のバージョンを正しく自動検出するためには、'lsb-release'パッケージがインストールされていることを確認する必要があります。これをプロビジョニングレイヤの一部にするか(多くのDebianシステムまたは派生OSシステムを実行する場合はこちらを推奨)、この依存関係を自動的に取得する機能をもつFacter 2.2.0以降をインストールしておくことを強くお勧めします。

## セットアップ

### aptが影響を与えるもの

* システムの`preferences`ファイルと`preferences.d`ディレクトリ
* システムの `sources.list`ファイルと`sources.list.d`ディレクトリ
* システムレポジトリ
* 認証キー

**注意:** このモジュールには`purge`パラメータがあります。このパラメータを`true`に設定すると、 ノードの `sources.list(.d)`および`preferences(.d)`の構成のうち、Puppetを通して宣言されていないものがすべて**破棄**されます。このパラメータのデフォルトは`false`です。

### aptの使用を開始する

デフォルトのパラメータでaptモジュールを使用するには、`apt`クラスを宣言します。

```puppet
include apt
```

**注意:** メインの`apt`クラスは、このモジュールに含まれるその他すべてのクラス、型、定義型によって要求されます。このモジュールを使用する際は、このクラスを必ず宣言する必要があります。

## 使用

### GPGキーの追加

**警告:** 短いキーIDを使用すると、衝突攻撃が有効になる可能性があり、セキュリティに深刻な問題が生じます。常に、完全なフィンガープリントを使用してGPGキーを識別することを推奨します。このモジュールでは短いキーの使用が許可されていますが、それを使用した場合、セキュリティ警告が発行されます。

`apt::key`の定義型を宣言するには、次のように記述します。

```puppet
apt::key { 'puppetlabs':
  id      => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
  server  => 'pgp.mit.edu',
  options => 'http-proxy="http://proxyuser:proxypass@example.org:3128"',
}
```

### バックポートの優先度を上げる

```puppet
class { 'apt::backports':
  pin => 500,
}
```

デフォルトでは、`apt::backports`クラスはバックポート用のピンファイルをドロップし、優先度200にピン止めします。これは、通常のデフォルト値である500よりも低いため、`ensure => latest`に設定されているパッケージは、明示的な許可がない限り、バックポートからアップグレードされることはありません。

`pin`パラメータを使用して優先度を500に上げると、通常のポリシーが有効になり、Aptは最新のバージョンをインストールするか、最新のバージョンにアップグレードします。これはつまり、`package`リソースの`ensure`属性を明示的に`installed`/`present`もしくは特定のバージョンに設定していない限り、あるパッケージがバックポートから利用できる場合は、そのパッケージと依存関係がバックポートから取得されるということです。

### パッケージリストの更新

デフォルトでは、`apt`クラスをインクルードした後の最初のPuppet実行時と、`notify  => Exec['apt_update']`が発生するたびに(別の言い方をすれば、構成ファイルが更新されるか、関連するその他の変更が行われるたびに)、Puppetは`apt-get update`を実行します。`update['frequency']`を'always'に設定すると、Puppet実行時に毎回更新が行われます。`update['frequency']`は'daily'や'weekly'に設定することも可能です。

```puppet
class { 'apt':
  update => {
    frequency => 'daily',
  },
}
```
`Exec['apt_update']`がトリガされると、`Notice`メッセージが生成されます。デフォルトの[agentロギングレベル](https://docs.puppet.com/puppet/latest/configuration.html#loglevel)は`notice`であるため、このレポジトリの更新は、ログおよびagentレポートに記録されます。[Foreman](https://www.theforeman.org)など、一部のツールでは、このような更新通知が重要な変更としてレポートされます。これらの更新がレポートに記録されないようにするには、`Exec['apt_update']`の[loglevel](https://docs.puppet.com/puppet/latest/metaparameter.html#loglevel)メタパラメータをagentロギングレベルよりも高い値に設定します。

```puppet
class { 'apt':
  update => {
    frequency => 'daily',
    loglevel  => 'debug',
  },
}
```

### 特定のリリースのピン止め

```puppet
apt::pin { 'karmic': priority => 700 }
apt::pin { 'karmic-updates': priority => 700 }
apt::pin { 'karmic-security': priority => 700 }
```

ディストリビューションのプロパティを使用して、より複雑なピンを指定することもできます。

```puppet
apt::pin { 'stable':
  priority        => -10,
  originator      => 'Debian',
  release_version => '3.0',
  component       => 'main',
  label           => 'Debian'
}
```

複数のパッケージをピン止めするには、配列またはスペース区切りの文字列としてその情報を`packages`パラメータに渡します。

### PPA (Personal Package Archive)レポジトリの追加

```puppet
apt::ppa { 'ppa:drizzle-developers/ppa': }
```

### `/etc/apt/sources.list.d/`へのAptソースの追加

```puppet
apt::source { 'debian_unstable':
  comment  => 'This is the iWeb Debian unstable mirror',
  location => 'http://debian.mirror.iweb.ca/debian/',
  release  => 'unstable',
  repos    => 'main contrib non-free',
  pin      => '-10',
  key      => {
    'id'     => 'A1BD8E9D78F7FE5C3E65D8AF8B48AD6246925553',
    'server' => 'subkeys.pgp.net',
  },
  include  => {
    'src' => true,
    'deb' => true,
  },
}
```

Puppet Aptレポジトリをソースとして使用するには、次のように記述します。

```puppet
apt::source { 'puppetlabs':
  location => 'http://apt.puppetlabs.com',
  repos    => 'main',
  key      => {
    'id'     => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
    'server' => 'pgp.mit.edu',
  },
}
```

### HieraからのAptの構成

ソースをリソースとして直接指定するかわりに、単純に`apt`クラスをインクルードして、値をHieraから自動的に取得するように構成できます。

```yaml
apt::sources:
  'debian_unstable':
    comment: 'This is the iWeb Debian unstable mirror'
    location: 'http://debian.mirror.iweb.ca/debian/'
    release: 'unstable'
    repos: 'main contrib non-free'
    pin: '-10'
    key:
      id: 'A1BD8E9D78F7FE5C3E65D8AF8B48AD6246925553'
      server: 'subkeys.pgp.net'
    include:
      src: true
      deb: true

  'puppetlabs':
    location: 'http://apt.puppetlabs.com'
    repos: 'main'
    key:
      id: '6F6B15509CF8E59E6E469F327F438280EF8D349F'
      server: 'pgp.mit.edu'
```

### デフォルトの`sources.list`ファイルの置き換え

デフォルトの`/etc/apt/sources.list`を置き換える例を以下に示します。以下のコードと合わせて、`purge`パラメータを必ず使用してください。使用しない場合、Apt実行時にソース重複の警告が出ます。

```puppet
apt::source { "archive.ubuntu.com-${lsbdistcodename}":
  location => 'http://archive.ubuntu.com/ubuntu',
  key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
  repos    => 'main universe multiverse restricted',
}

apt::source { "archive.ubuntu.com-${lsbdistcodename}-security":
  location => 'http://archive.ubuntu.com/ubuntu',
  key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
  repos    => 'main universe multiverse restricted',
  release  => "${lsbdistcodename}-security"
}

apt::source { "archive.ubuntu.com-${lsbdistcodename}-updates":
  location => 'http://archive.ubuntu.com/ubuntu',
  key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
  repos    => 'main universe multiverse restricted',
  release  => "${lsbdistcodename}-updates"
}

apt::source { "archive.ubuntu.com-${lsbdistcodename}-backports":
 location => 'http://archive.ubuntu.com/ubuntu',
 key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
 repos    => 'main universe multiverse restricted',
 release  => "${lsbdistcodename}-backports"
}
```

### APTソースやプロキシのログイン設定を`/etc/apt/auth.conf`で管理する

APTバージョン1.5以降、認証が必要なAPTソースやプロキシについて、ユーザ名やパスワードなどのログイン設定を`/etc/apt/auth.conf`ファイルに定義できるようになりました。この方法は、`source.list`内にログイン情報を直接記述するよりも推奨されます。直接記述した場合、通常、あらゆるユーザから読み取り可能になるためです。

`/etc/apt/auth.confファイルのフォーマットは、(ftpやcurlによって使用される) netrcに従い、ファイルパーミッションが制限されています。詳しくは、[こちら](https://manpages.debian.org/testing/apt/apt_auth.conf.5.en.html)を参照してください。

オプションの`apt::auth_conf_entries`パラメータを使用して、ログイン設定を含むハッシュの配列を指定します。このハッシュに含めることができるのは、`machine`、`login`、および`password`キーのみです。

```puppet
class { 'apt':
  auth_conf_entries => [
    {
      'machine'  => 'apt-proxy.example.net',
      'login'    => 'proxylogin',
      'password' => 'proxypassword',
    },
    {
      'machine'  => 'apt.example.com/ubuntu',
      'login'    => 'reader',
      'password' => 'supersecret',
    },
  ],
}
```

## リファレンス

### Facts

* `apt_updates`: `upgrade`で入手可能な更新がある、インストール済みパッケージの数。

* `apt_dist_updates`: `dist-upgrade`で入手可能な更新がある、インストール済みパッケージの数。

* `apt_security_updates`: `upgrade`で入手可能なセキュリティ更新がある、インストール済みパッケージの数。

* `apt_security_dist_updates`: `dist-upgrade`で入手可能なセキュリティ更新がある、インストール済みパッケージの数。

* `apt_package_updates`: `upgrade`で入手可能な更新がある、すべてのインストール済みパッケージの名前。Facter 2.0以降では、このデータのフォーマットは配列で、それ以前のバージョンでは、コンマ区切りの文字列です。

* `apt_package_dist_updates`: `dist-upgrade`で入手可能な更新がある、すべてのインストール済みパッケージの名前。Facter 2.0以降では、このデータのフォーマットは配列で、それ以前のバージョンでは、コンマ区切りの文字列です。

* `apt_update_last_success`: 直近で成功した`apt-get update`実行のエポックタイムによる日付(/var/lib/apt/periodic/update-success-stampのmtimeに基づく)。

* `apt_reboot_required`: 更新がインストールされた後に再起動が必要かどうかを決定します。

### 詳細情報

その他すべてのリファレンスマニュアルについては、[REFERENCE.md](https://github.com/puppetlabs/puppetlabs-apt/blob/master/REFERENCE.md)を参照してください。

## 制約

このモジュールは、[実行ステージ](https://docs.puppetlabs.com/puppet/latest/reference/lang_run_stages.html)に分割するようには設計されていません。

サポート対象のオペレーティングシステムの全リストについては、[metadata.json](https://github.com/puppetlabs/puppetlabs-apt/blob/master/metadata.json)を参照してください。

### 新しいソースまたはPPAの追加

新しいソースまたはPPAを追加し、同一のPuppet実行において、その新しいソースまたはPPAからパッケージをインストールするには、`package`リソースが`Apt::Source`または`Apt::Ppa`に従属し、かつ`Class['apt::update']に従属する必要があります。[コレクタ](https://docs.puppetlabs.com/puppet/latest/reference/lang_collectors.html)を追加することによって、すべてのパッケージが`apt::update`の後に来るように制御することもできますが、その場合、循環依存が発生したり、[仮想リソース](https://docs.puppetlabs.com/puppet/latest/reference/lang_collectors.html#behavior)と関係したりすることがあります。以下のコマンドを実行する前に、すべてのパッケージのプロバイダがaptに設定されていることを確認してください。

```puppet
Class['apt::update'] -> Package <| provider == 'apt' |>
```

## 開発

Puppet ForgeのPuppet Labsモジュールはオープンプロジェクトで、良い状態に保つためには、コミュニティの貢献が必要不可欠です。Puppetが役に立つはずでありながら、私たちがアクセスできないプラットフォームやハードウェア、ソフトウェア、デプロイ構成は無数にあります。私たちの目標は、できる限り簡単に変更に貢献し、みなさまの環境で私たちのモジュールが機能できるようにすることにあります。最高の状態を維持できるようにするために、コントリビュータが従う必要のあるいくつかのガイドラインが存在します。

詳細については、[モジュール貢献ガイド](https://docs.puppetlabs.com/forge/contributing.html)を参照してください。

すでにご協力いただいている方のリストについては、[コントリビュータのリスト](https://github.com/puppetlabs/puppetlabs-apt/graphs/contributors)をご覧ください。
