[English](README.md) | 简体中文

## Alibaba Cloud NLS SDK

## 安装

### Composer

```bash
composer require alibabacloud/nls-sdk
```

## 问题

[提交 Issue](https://github.com/aliyun/alibabacloud-nls-php-sdk/issues/new)，不符合指南的问题可能会>立即关闭。

## 发行说明

每个版本的详细更改记录在[发行说明](./ChangeLog.txt)中。

## 相关

* [智能语音交互接入](https://help.aliyun.com/document_detail/72138.html)
* [最新源码](https://github.com/aliyun/alibabacloud-nls-php-sdk)

## 示例说明

### 语音合成示例说明

启动本地服务，通过设置的本地通信端口号从本地客户端接收指令和与远端语音服务器进行通信。
```bash
php demo/speechSynthesizerService.php start <本地通信端口号>
```
启动本地客户端，通过设置的本地通信端口号向已经启动的本地服务发送指令。注意：本地通信端口需要一一对应，不同组语音请求设置的本地服务端口须不同。
```bash
php demo/speechSynthesizerDemo.php <你的akId> <你的akSecret> <你的appkey> <本地通信端口号>
```

### 一句话语音识别示例说明

启动本地服务，通过设置的本地通信端口号从本地客户端接收指令和与远端语音服务器进行通信。
```bash
php demo/speechRecognizerService.php start <本地通信端口号>
```
启动本地客户端，通过设置的本地通信端口号向已经启动的本地服务发送指令。注意：本地通信端口需要一一对应，不同组语音请求设置的本地服务端口须不同。
```bash
php demo/speechRecognizerDemo.php <你的akId> <你的akSecret> <你的appkey> <本地通信端口号>
```

### 实时语音识别示例说明

启动本地服务，通过设置的本地通信端口号从本地客户端接收指令和与远端语音服务器进行通信。
```bash
php demo/speechTranscriberService.php start <本地通信端口号>
```
启动本地客户端，通过设置的本地通信端口号向已经启动的本地服务发送指令。注意：本地通信端口需要一一对应，不同组语音请求设置的本地服务端口须不同。
```bash
php demo/speechTranscriberDemo.php <你的akId> <你的akSecret> <你的appkey> <本地通信端口号>
```

## 许可证

[Apache-2.0](http://www.apache.org/licenses/LICENSE-2.0)

Copyright (c) 2009-present, Alibaba Cloud All rights reserved.
