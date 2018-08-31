# AipSpeechVoice
```

    小程序 发送语音 > 
    上传mp3文件到服务器 > 
    服务器FFmpeg mp3转码wav > 
    调取百度语音识别接口 > 
    返回识别文字 >
    接入自己逻辑流程>
    end

```

### chat.js
```js
    // 录音对象
    const mp3Recorder = wx.getRecorderManager()
    
    const mp3RecoderOptions = {
        duration: 5000, //2s
        sampleRate: 16000, // 采样率
        numberOfChannels: 1, // 单声道
        audioSource: "auto", // 所有系统机型
        format: "mp3",
        encodeBitRate: 24000 //16bit
        //frameSize: 50
    };
    
    Page({
        data: {
            message: [], //存储对话
            inputMsg: "" //input框输入的内容value
        },
        startHandel: function () {
            // 按下按钮--录音
            console.log("mp3Recorder.start with" + mp3RecoderOptions);
    
            // var reply = {};
            // reply = {
            //     type: 1,
            //     src: "https://aicp.jkbk.cn/assets/img/yjl/images/profle.jpg",
            //     state: 0,
            //     content: "开始"
            // };
            // this.setMessage(reply);
    
            mp3Recorder.start(mp3RecoderOptions);
        },
        endHandel: function () {
            // 松开按钮
            console.log("mp3Recorder.stop");
    
            // var reply = {};
            // reply = {
            //     type: 1,
            //     src: "https://aicp.jkbk.cn/assets/img/yjl/images/profle.jpg",
            //     state: 0,
            //     content: "结束"
            // };
            // this.setMessage(reply);
    
            // 触发录音停止
            mp3Recorder.stop();
        },
        sendRecord: function (tempFilePath) {
            var that = this;
            var obj = {
                url: "https://aicp.jkbk.cn/api/speechvoice/wxupload",
                filePath: tempFilePath,
                name: "voice",
                header: {
                    "Content-Type": "application/json"
                },
                success: function (e) {
    
                    var data = JSON.parse(e.data);
    
                    that.postTextAjax(data.query_text);
                },
                fail: function (err) {
                    console.log(err);
                }
            };
            console.log(obj);
    
            // 上传录制的音频
            wx.uploadFile(obj);
        },
        setMessage: function (msg) {
            var msgList = this.data.message;
            msgList.push(msg);
            this.setData({
                message: msgList,
                inputMsg: ""
            });
        },
    });
```

### wxml
```xhtml
    <view class="sendYyMessage">
        <view class="jp-btn" bindtap="showTextBtn">
            <image src="https://aicp.jkbk.cn/assets/img/yjl/images/jp_btn.png"></image>
        </view>
    
        <view class="wenwen_text">
            <view class="circle-button" bind:touchstart="startHandel" bind:touchend="endHandel">按下录音 松开结束</view>
        </view>
    </view>
```

### FFmpeg
```markdown
    [CentOS_7.4]Linux编译安装ffmpeg
        安装过程：
        1# 安装yasm
        
        1 # wget http://www.tortall.net/projects/yasm/releases/yasm-1.3.0.tar.gz
        2 # tar -zxvf yasm-1.3.0.tar.gz
        3 # cd yasm-1.3.0
        4 # ./configure
        5 # make && make install
        
        2下载安装源，配置，编译，安装，设置环境变量。
        
        1 # wget http://www.ffmpeg.org/releases/ffmpeg-3.1.tar.gz
        2 # tar -zxvf ffmpeg-3.1.tar.gz
        3 # cd ffmpeg-3.1
        4 # ./configure --prefix=/usr/local/ffmpeg
        5 # make && make install
        6
        7 等待安装完成...
        8
        9 # vi /etc/profile
        10 在最后PATH添加环境变量：
        11 PATH=$PATH:/usr/local/ffmpeg/bin
        12 export PATH
        13 保存退出
        14
        15 # source /ect/profile   设置生效
        16 # ffmpeg -version       查看版本
```

### Controller 自行查阅
```php
    echo '自行查阅';
```
