import VideoContext from "./videocontext";
import TimedPromise from "./timed_promise";
import { fabric } from "fabric";
import store from './store'


class ScenePreview {
  constructor() {
    this.row = null;
    this.RS = 0;
    window.Assets = {};
    this.i=0;
    this.changedObject = {};
    this.selectionRect=null;
    this.currentImage;
    this.rowObject = {};

  }
  getCombineDescription(left = 0, top = 0, width = 1, height = 1) {
    const w = 2.0 * width;
    const h = 2.0 * height;
    const l = 1.0 - left * 2.0;
    const t = -1 + (top + height) * 2.0;

    return {
      title: "Combine",
      description:
        "A basic effect which renders the input to the output, Typically used as a combine node for layering up media with alpha transparency.",
      vertexShader: `
          attribute vec2 a_position;
          attribute vec2 a_texCoord;
          varying vec2 v_texCoord;
          void main() {
            gl_Position = vec4(vec2(${w},${h}) * a_position - vec2(${l}, ${t}), 0.0, 1.0);
            v_texCoord = a_texCoord;
          }`,
      fragmentShader: `
          precision mediump float;
          uniform sampler2D u_image;
          varying vec2 v_texCoord;
          varying float v_mix;
          void main(){
            vec4 color = texture2D(u_image, v_texCoord);
            gl_FragColor = color;
          }`,
      properties: {},
      inputs: ["u_image"],
    };
  }
  getBorderDescription(left = 0, top = 0, width = 100, height = 100) {
    const w = (2.0 * width) / 100;
    const h = (2.0 * height) / 100;
    const l = 1.0 - left * 2.0;
    const t = -1 + (top + height) * 2.0;

    return {
      title: "BorderCombine",
      description: "border combine",
      vertexShader: `
          attribute vec2 a_position;
          attribute vec2 a_texCoord;
          varying vec2 v_texCoord;
          void main() {
            gl_Position = vec4(vec2(${w},${h}) * a_position - vec2(${l}, ${t}), 0.0, 1.0);
            v_texCoord = a_texCoord;
          }`,
      fragmentShader: `
          precision mediump float;
          uniform sampler2D u_image;
          varying vec2 v_texCoord;
          varying float v_mix;
          void main(){
            vec4 color = texture2D(u_image, v_texCoord);
            gl_FragColor = color;
          }`,
      properties: {},
      inputs: ["u_image"],
    };
  }

  getStarSlideDescription(rate) {
    return {
      title: "Monochrome",
      description:
        "Change images to a single chroma (e.g can be used to make a black & white filter). Input color mix and output color mix can be adjusted.",
      vertexShader: `
          attribute vec2 a_position;
          attribute vec2 a_texCoord;
          varying vec2 v_texCoord;
          void main() {
              gl_Position = vec4(vec2(2.0,2.0)*a_position-vec2(1.0, 1.0), 0.0, 1.0);
              v_texCoord = a_texCoord;
          }`,
      fragmentShader: `
          precision mediump float;
          uniform sampler2D u_image;
          varying vec2 v_texCoord;
          varying float v_mix;
          uniform float rate;
          void main(){
            vec4 color = texture2D(u_image, v_texCoord);
  
            if (v_texCoord[0] < rate) {
              gl_FragColor = color;
            } else {
              gl_FragColor = vec4(color.r, 0.9, 0.9, color.a);
            }
          }`,
      properties: {
        rate: { type: "uniform", value: rate },
      },
      inputs: ["u_image"],
    };
  }

  isStringStar(str) {
    return str
      .substring(0, 5)
      .split("")
      .every((char) => char === "★");
  }

  resetCtx() {
    if (this.vCtx) this.vCtx.reset();
  }

  initCanvas(canvas) {
    const ctx = canvas.getContext("2d");
    ctx.fillStyle = "white";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
  }

  async init(row, elementId,modalstatus) {
    if (row) {
      //old
      this.canvas = document.getElementById(elementId);
      this.vCtx = new VideoContext(this.canvas);
      //new
      if(this.i==0 || modalstatus==1){
        this.NewCanvas =  new fabric.Canvas('scene-editor-New');
        this.i=1;
        
      }else{
          this.NewCanvas.clear();
      }


      this.totalDuration = +row.End;
      // duration shits
      row.Start = +row.Start;
      row.End = +row.End;
      if (row.Type == "text") {

        const { width, height } = this.getTextRect(row);
        const txtHeight = height;
        const txtWidth = width;
        row.textHeight = height;

        let top = (this.canvas.height - txtHeight) / 2.0;
        


        if (row.Type == "Text") {
          row.Top = top;
          if (row.AlignH == "Left") row.Left = 1;
          else {
            row.Left =
              this.canvas.width * row.Left +
              (this.canvas.width * row.Width - txtWidth) / 2;
          }
          top = top + txtHeight;
        }

      }

      this.row = row;

      return this.NewCanvas.on('object:modified', function(e) {
        return e;      
      }); 
    }
  }

  getUpdatedValue() {
    return this.row;
  }

  getSceneMeta(row) {
    let direction = "";
    let type = "";

    if (row.Type == "Text") {
      direction = "v";
      type = "text";
    } else if (row.Type == "Image") {
      if ((row.Width == 1 || row.Width == 0.5) && row.Height < 1.0) {
        direction = "v";
      }

      if (row.Height == 1 && row.Width < 1.0) {
        direction = "h";
      }
      type = "image";
    }

    return { direction, type };
  }

  async parseScene(time,BWidth,BHeight,BLeft,BTop,selectLeft,selectTop) {
    const row = this.row;
    switch (row.Type) {
      case "Background": {
        const { canvas } = this.createBlankCanvas(row.sceneId);

        const node = this.vCtx.canvas(canvas);
        node.start(0);
        node.stop(this.totalDuration);

        const combineEffect = this.vCtx.compositor(
          this.getCombineDescription()
        );
        node.connect(combineEffect);
        combineEffect.connect(this.vCtx.destination);
        break;
      }

      case "Image": {
        let canvas0;

        if (row.Animation && row.Animation != "none") {
          const { canvas } = this.createBlankCanvas(row.sceneId);

          canvas0 = canvas;
        }

        const node = this.vCtx.image(row.Filename);

       var objs = this.NewCanvas.getObjects();
        if (objs.length=='0') { 
          this.NewCanvas.clear();
        fabric.Image.fromURL(row.Filename, img => {
              img.scaleToHeight(BHeight);
              img.selectable = true;
              this.NewCanvas.add(img);
              img.left=BLeft;
              img.top=this.canvas.height * row.Top;
              img.scaleToWidth(BWidth);
              img.scaleToHeight(BHeight);
              // this.NewCanvas.centerObject(img);
              this.currentImage = img;                      
              // this.addSelectionRect();
              // this.NewCanvas.setActiveObject(this.selectionRect);                      
              });
            }

       
        node.start(row.Start);
        node.stop(row.End);

        const combineEffect = this.vCtx.compositor(
          this.getCombineDescription(row.Left, row.Top, row.Width, row.Height)
        );

        if (row.Animation && row.Animation != "none") {
          const fakeNode = this.vCtx.canvas(canvas0);
          fakeNode.start(row.Start);
          fakeNode.stop(row.End);

          let transition;
          if (row.Animation == "fadein") {
            transition = this.vCtx.transition(
              VideoContext.DEFINITIONS.CROSSFADE
            );
            transition.transition(row.Start, row.End, 0.0, 1.0, "mix");
            fakeNode.connect(transition);
            node.connect(transition);
          } else if (row.Animation == "fadeout") {
            transition = this.vCtx.transition(
              VideoContext.DEFINITIONS.CROSSFADE
            );
            transition.transition(row.Start, row.End, 0.0, 1.0, "mix");
            node.connect(transition);
            fakeNode.connect(transition);
          }

          transition.connect(combineEffect);
        } else {
          node.connect(combineEffect);
        }

        combineEffect.connect(this.vCtx.destination);
        break;
      }
      case "Text": {
        let canvas0;
        
        if (row.Animation && row.Animation != "none") {
          const { canvas } = this.createBlankCanvas(row.sceneId);
          canvas0 = canvas;
        }

        const { canvas, ctx } = this.createBlankCanvas(row.sceneId);
        const txt = this.isStringStar(row.Text)
          ? row.Text.substring(0, 5)
          : row.Text;
          
          ctx.font = `${row.Size * this.RS}px ${row.Font_Name}`;
          ctx.fillStyle = 'black';
          ctx.textBaseline = "middle";
          ctx.fillText(txt, row.Left, row.Top);
          const { width, height } = this.getTextRect(row);

          const len = 5;

          let maxTextWidth = Math.max(0, width);
          const txtHeight = height;
          let top = (this.canvas.height - txtHeight * len) / 2.0;
                    
          
          if (row.Top === "" ||row.Top==="NaN") {
            row.Top = top;
            top = top + txtHeight + Math.floor(Math.random() * 5);
          } else {
            row.Top =
              (row.Top + row.Height / 2) * this.canvas.height -
              txtHeight / 2;
          }

          if (row.AlignH == "Left") row.Left = 1;
              else {
                row.Left =
                  this.canvas.width * row.Left +
                  (this.canvas.width * row.Width - maxTextWidth) / 2;
              }
              let mainLeft=0;
              let mainTop=0;
              if(selectLeft==0 && selectTop==0){
                 mainLeft=row.Left;
                 mainTop=row.Top;
              }else
              {
                if(selectLeft % 1 !=0){
                  mainLeft=selectLeft;
                  mainTop=selectTop;
                }else{
                  mainLeft=selectLeft;
                  mainTop=selectTop;
                }
              }

         
        this.NewCanvas.clear();
          var textbox1 = new fabric.IText(row.Text, {
            left: mainLeft, 
            top: mainTop, 
            fontSize: 20, 
            fontFamily: 'Times New Roman', 
            fontWeight: 'normal',
            lineHeight: 1,
            fill: "black",
            
          });
          this.NewCanvas.add(textbox1);

        this.NewCanvas.on('object:modified', function(e) {
          if(store.getters['sceneDialog/getscene'].Color){          
            store.getters['sceneDialog/getscene'].Width=e.target.width;
            store.getters['sceneDialog/getscene'].Height=e.target.height;
            store.getters['sceneDialog/getscene'].Top=e.target.top;
            store.getters['sceneDialog/getscene'].Left=e.target.left;
          }
          else
          {
            console.log('please select Border');
          }

        });
    

        const node = this.vCtx.canvas(canvas);
        node.start(row.Start);
        node.stop(row.End);

        const combineEffect = this.vCtx.compositor(
          this.getCombineDescription()
        );

        if (row.Animation && row.Animation != "none") {
          const fakeNode = this.vCtx.canvas(canvas0);

          fakeNode.start(row.Start);
          fakeNode.stop(row.End);

          let transition;

          if (row.Animation == "star") {
            let rate = row.Text.substring(5) ? row.Text.substring(5) : "5.0";
            rate = parseFloat(rate) / 5.0;
            const starEffect = this.vCtx.effect(
              this.getStarSlideDescription(
                (row.Left + rate * row.textWidth) / canvas.width
              )
            );

            transition = this.vCtx.transition(
              VideoContext.DEFINITIONS.HORIZONTAL_WIPE
            );
            transition.transition(row.Start, row.End, 0.0, 1.0, "mix");
            fakeNode.connect(transition);
            node.connect(starEffect);
            starEffect.connect(transition);
          } else if (row.Animation == "fadein") {
            transition = this.vCtx.transition(
              VideoContext.DEFINITIONS.CROSSFADE
            );
            transition.transition(row.Start, row.End, 0.0, 1.0, "mix");
            fakeNode.connect(transition);
            node.connect(transition);
          } else if (row.Animation == "fadeout") {
            transition = this.vCtx.transition(
              VideoContext.DEFINITIONS.CROSSFADE
            );
            transition.transition(row.Start, row.End, 0.0, 1.0, "mix");
            node.connect(transition);
            fakeNode.connect(transition);
          }

          transition.connect(combineEffect);
        } else {
          if (this.isStringStar(row.Text)) {
            let rate = row.Text.substring(5) ? row.Text.substring(5) : "5.0";
            rate = parseFloat(rate) / 5.0;
            const starEffect = this.vCtx.effect(
              this.getStarSlideDescription(
                (row.Left + rate * row.textWidth) / canvas.width
              )
            );

            node.connect(starEffect);
            starEffect.connect(combineEffect);
          } else {
            node.connect(combineEffect);
          }
        }
        

        combineEffect.connect(this.vCtx.destination);
        break;
      }
      default:
        throw Error("unknown row type");
    }
    this.NewCanvas.renderAll();
  }

  createBlankCanvas(isFill = false) {
    const canvas = document.createElement("canvas");
    canvas.width = this.canvas.width;
    canvas.height = this.canvas.height;
    const ctx = canvas.getContext("2d");

    if (isFill) {
      ctx.fillStyle = "white";
      ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    return { canvas, ctx };
  }

  loadImageAsset(fileName) {
    return new TimedPromise(10000, (resolve, reject) => {
      const asset = document.createElement("img");
      asset.src = fileName;
      asset.onload = () => {
        resolve({ fileName, asset });
      };
    });
  }

  loadVideoAsset(fileName) {
    return new TimedPromise(30000, (resolve, reject) => {
      const asset = document.createElement("video");
      asset.src = fileName;
      asset.muted = true;
      asset.load();
      asset.oncanplaythrough = () => {
        resolve({ fileName, asset });
      };
    });
  }

  loadAllFonts() {
    return new Promise((resolve, reject) => {
      document.fonts.ready
        .then(() => {
          resolve({});
        })
        .catch((err) => {
          console.log("loading fonts failed", err);
          reject(err);
        });
    });
  }

  loadAssets() {
    const row = this.row;
    if (row && row.Type == "Image") {
      return Promise.all([
        this.loadAllFonts(),
        this.loadImageAsset(row.Filename),
      ]);
    }
    return Promise.all([this.loadAllFonts()]);
  }

  play() {
    this.currentTime = 0;
    this.vCtx.play();
  }

  stop() {
    if (this.vCtx) {
      this.currentTime = 0;
      this.vCtx.pause();
      this.resetCtx();
    }
  }

  pause() {
    this.vCtx.pause();
  }

  resume() {
    this.vCtx.play();
  }

  get currentTime() {
    if (this.vCtx) return this.vCtx.currentTime;
    else return 0;
  }

  set currentTime(t = 0) {
    if (this.vCtx) return (this.vCtx.currentTime = t);
  }

  get state() {
    if (this.vCtx) {
      if (this.vCtx.state == 0) return "PLAYING";
      else if (this.vCtx.state == 1) return "PAUSED";
      else if (this.vCtx.state == 3) return "ENDED";
      else return "OTHER";
    } else {
      return "NO";
    }
  }

  setRS(rs) {
    this.RS = rs;
  }

  getTextRect(row) {
    const marginY = -10;
    const fontSize = row.Size * this.RS;
    const t = document.getElementById("test-text");
    t.style.fontSize = `${fontSize}px`;
    t.style.fontFamily = row.Font_Name;
    t.innerHTML = this.isStringStar(row.Text) ? "★★★★★" : row.Text;

    return {
      width: t.clientWidth + 1,
      height: t.clientHeight + marginY,
    };
  }

  getImageRect(row) {
    return new Promise((resolve, reject) => {
      const img = new Image();

      img.onload = () =>
        resolve({
          width: img.width + 1,
          height: img.height + 1,
        });
      img.onerror = reject;
      img.src = row.Filename;
    });
  }
}

export default ScenePreview;
