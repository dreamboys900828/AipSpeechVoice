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
        //     src: "https://xxxx.xxxx.cn/assets/img/yjl/images/profle.jpg",
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
        //     src: "https://xxxx.xxxx.cn/assets/img/yjl/images/profle.jpg",
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
            url: "https://xxxx.xxxx.cn/api/speechvoice/wxupload",
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
