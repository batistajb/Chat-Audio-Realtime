<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Chat</div>
                    <div class="card-body">
                       <div class="row">
                           <div class="col-md-4">
                               <ul class="nav navbar-nav">
                                   <li class="page page-item" v-for="user in users">
                                       <a class="page page-link" @click="loadMessages(user)"> {{user.name}}</a>
                                   </li>
                               </ul>
                           </div>
                           <div class="col-md-8" v-if="!started">

                               <div class="card bg-warning" v-if="!loading">
                                   <div class="card card-header">
                                       Falando com <strong>{{currentChat.name}}</strong>
                                   </div>
                               </div>

                               <div class="card" :class="{'card-warning':currentChat.id===message.sender}" v-for="message in messages" >
                                   <div class="card card-body">
                                       <audio controls>
                                           <source :src="'/audios/'+message.audio">
                                       </audio>
                                   </div>
                               </div>

                               <a id="rec" :class="{'recording':recording}" @click="rec()"></a>

                               <div class="card bg-info" v-if="messages.length === 0">
                                   <div class="card card-body">
                                      Nenhuma menssagem para exibir.
                                   </div>
                               </div>

                           </div>

                           <div class="col-md-8" v-if="started">
                               <div class="card bg-success" v-if="!loading">
                                   <div class="card card-header">
                                       Escolha alguém para conversar!
                                   </div>
                               </div>
                               <div class="card badge-info" v-if="loading">
                                   <div class="card card-header">
                                       Obtendo conversa...
                                   </div>
                               </div>
                               <div class="card badge-danger" v-if="error">
                                   <div class="card card-header">
                                       Erro ao carregar conversa...
                                   </div>
                               </div>
                           </div>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                started:true,
                loading:false,
                error:false,
                recording:false,
                recorder:null,
                recordedData:[],
                currentChat:{},
                messages:[],
                users:[]
            }
        },
        methods:{
            loadMessages(user){
                this.loading=true;
                this.started=true;

                window.axios.get('/message/' + user.id)
                    .then((response)=>{
                        this.currentChat = user;
                        this.loading=false;
                        this.started=false;
                        this.messages=response.data;
                    }).catch(()=>{
                    this.loading=false;
                    this.error=true;
                });
            },
            rec(){
                this.recording = !this.recording;
                if(this.recording){
                    this.startRec();
                }else{
                    this.stopRec();
                }
            },
            startRec(){
                const config ={
                    audio: true,
                    video: false
                };

                const successCalback = (stream) => {
                    this.recorder = new MediaRecorder(stream);

                    this.recorder.ondataavailable = (e)=>{
                        this.recordedData.push(e.data);
                        console.log(this.recordedData);
                    };

                    this.recorder.onstop = () => {
                        let blob = new Blob(this.recordedData, {type:'video/webm'});

                        this.recorder = null;
                        this.recordedData =[];

                        let formData = new FormData();
                        formData.append('audio',blob);
                        formData.append('receiver',this.currentChat.id);

                        window.axios.post('/message',formData).then((response)=>{
                            console.log('done');
                            this.messages.splice(0,0,response.data);
                        })
                    };

                    this.recorder.start();
                };

                const errorCalback = (err) => {
                    console.log(err);
                };

                navigator.getUserMedia(config, successCalback, errorCalback);

                console.log('gravando');
            },
            stopRec(){
                this.recorder.stop();
            },

        },
        mounted() {
            window.axios.get('/users').then((response)=>{
                this.users = response.data;
            });

            Echo.channel('channel.messages.'+ me)
                .listen('AudioSend',(e)=>{
                    console.log(e);
                    if(e.message.sender === this.currentChat.id){
                        this.messages.splice(0,0,e.message);
                    }
                });
        }
    }
</script>

<style>
    @keyframes pulse {
       50%{
           background: transparent;
       }
    }
    a {
        cursor: pointer;
    }

    #rec {
     position: fixed;
        right: 10px;
        bottom: 10px;
    }
    #rec:after {
    display:block;
        content: '';
        width: 30px;
        height: 30px;
        border-radius: 30px;
        background: red;
    }

    #rec.recording:after{
        animation: pulse 1s infinite;
    }


</style>
