import VideoContext from "./videocontext";
import {
  getValueMeasurementLineSpacing,
} from "./services/helpers";
class TimedPromise extends Promise {
  constructor(timeout, callback) {
    // We need to support being called with no milliseconds
    // value, because the various Promise methods (`then` and
    // such) correctly call the subclass constructor when
    // building the new promises they return.
    const haveTimeout = typeof timeout === "number";
    const init = haveTimeout ? callback : timeout;

    super((resolve, reject) => {
      if (haveTimeout) {
        const timer = setTimeout(() => {
          reject(new Error(`Promise timed out after ${timeout}ms`));
        }, timeout);

        init(
          (value) => {
            clearTimeout(timer);
            resolve(value);
          },
          (error) => {
            clearTimeout(timer);
            reject(error);
          }
        );
      } else {
        init(resolve, reject);
      }
    });
  }
  // Pick your own name of course. (You could even override `resolve` itself
  // if you liked; just be sure to do the same arguments detection we do
  // above in the constructor, since you need to support the standard use of
  // `resolve`.)
  static resolveWithTimeout(timeout, x) {
    if (!x || typeof x.then !== "function") {
      // `x` isn't a thenable, no need for the timeout,
      // fulfill immediately
      return this.resolve(x);
    }

    return new this(timeout, x.then.bind(x));
  }
}

class Preview {
  constructor() {
    this.rows = [];
    this.RS = 0;
    window.Assets = {};
    this.fadeinout='';
  }

  getCombineDescription(left = 0, top = 0, width = 1, height = 1) {
    const w = 2.0 * width;
    const h = 2.0 * height;
    const l = 1.0 - left * 2.0;
    const t = -1.0 + (top + height) * 2.0;

    return {
      title: "Combine",
      description:
        "A basic effect which renders the input to the output, Typically used as a combine node for layering up media with alpha transparency.",
      vertexShader: `
          attribute vec2 a_position;
          attribute vec2 a_texCoord;
          varying vec2 v_texCoord;
          void main() {
            gl_Position = vec4(vec2(${w},${h})*a_position-vec2(${l},${t}), 0.0, 1.0);
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
    if (typeof str !== 'string') return false;

    return str
      .substring(0, 5)
      .split("")
      .every((char) => char === "★");
  }

  resetCtx() {
    if (this.vCtx) this.vCtx.reset();
  }

  async init(rows) {
    let subsceneId = -1;
    let sceneInd = -1;
    let maxDurationForScene = 0;
    let prevDuration = 0;
    let textRows = [];
    let imgRows = [];
    let maxTextWidth = 0;
    let totalTextHeight = 0;
    let { direction, type } = this.getSceneMeta(rows[0]);
    this.drawOnceBG = 0;
    this.currentScene = undefined;
    this.totalDuration = 0;
    this.alignH = {
      leftFF:'Left-FF',
      leftLH:'Left-LH',
      leftRH:'Left-RH',
      rightFF:'Right-FF',
      rightLH:'Right-LH',
      rightRH:'Right-RH',
      centerFF:'Center-FF',
      centerLH:'Center-LH',
      centerRH:'Center-RH',
      fF:'FF',
      lH:'LH',
      rH:'RH',
    };
    this.alignV = {
      center: 'Center',
      top: 'Top',
      bottom: 'Bottom',
    };
    this.canvas = document.getElementById("preview");
    this.vCtx = new VideoContext(this.canvas);

    this.isMusicAvailable = false;

    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      subsceneId = `${row.Scene}-${row.Subscene}`;

      if (row.Type == "Music") {
        this.isMusicAvailable = true;
      } else {
        // duration shits
        if (sceneInd != row.Scene) {
          sceneInd = row.Scene;
          prevDuration += maxDurationForScene;
          maxDurationForScene = 0;
        }

        maxDurationForScene = Math.max(maxDurationForScene, +row.End);

        row.Start = +row.Start + prevDuration;
        row.End = +row.End + prevDuration;

        if (type == "text") {
          const { width, height } = this.getTextRect(row);

          row.textHeight = height;
          maxTextWidth = Math.max(maxTextWidth, width);
          textRows.push(row);
        } else if (type == "image") {
          imgRows.push(row);
        }
      }

      if (
        i == rows.length - 1 ||
        (rows[i + 1] &&
          subsceneId != `${rows[i + 1].Scene}-${rows[i + 1].Subscene}`)
      ) {
        if (textRows.length) {
          const len = textRows.filter(row => {
           return  !!row.Text && row.Type === "Text"
          }).length;
          const txtHeight = textRows[0].textHeight;

          let top = len > 1 ?
              (this.canvas.height - (txtHeight / 1.35) * len) / 2.0
              : this.canvas.height / 2.0;

          for (const row of textRows) {
            if (row.Type == "Text") {
              row.Top = this.calculateVerticalPositionOfText(row, top, textRows, rows);
              row.Left = this.calculateHorizontalPositionOfText(row, maxTextWidth);
            } else if (row.Type == "Image") {
              const { width, height } = await this.getImageRect(row);
              const factor = txtHeight / height;
              if (row.Left) {
                row.Left = (this.canvas.width * row.Left - factor) / 2 / this.canvas.width;
              } else {
                row.Left = (this.canvas.width * row.Width - factor * width) / 2 / this.canvas.width;
              }
              if (row.Top) {
                row.Top = ((top - 0) / this.canvas.height) * row.Top;
              } else {
                row.Top = ((top - 0) / this.canvas.height) * 0.9;
              }

              let widthOfImageWithPercent = (row.Width * width);
              let heightOfImageWithPercent = (row.Height * height);
              row.Width =  (widthOfImageWithPercent / this.canvas.width);
              row.Height =  (heightOfImageWithPercent / this.canvas.height);
              switch (this.canvas.width) {
                case 854 :
                  row.Width = row.Width / (1920 / 854);
                  row.Height = row.Height / (1080 / 480);
                  break;
                case 640 :
                  row.Width = row.Width / (1920 / 640);
                  row.Height = row.Height / (1080 / 360) ;
                  break;
                case 426 :
                  row.Width = row.Width / (1920 / 426);
                  row.Height = row.Height /  (1080 / 240);
                  break;
                default: break;
              }
              top = top + factor * height;
            }
          }

          textRows = [];
          maxTextWidth = 0;
          totalTextHeight = 0;
        }

        if (imgRows.length) {
          imgRows.forEach((r, j, _rows) => {
            if (j == 0) return;

            if (direction == "v") {
              r.Top = _rows[j - 1].Top + _rows[j - 1].Height;
            } else if (direction == "h") {
              r.Left = _rows[j - 1].Left + _rows[j - 1].Width;
            }
          });

          imgRows = [];
        }

        if (rows[i + 1]) {
          const obj = this.getSceneMeta(rows[i + 1]);
          direction = obj.direction;
          type = obj.type;
        }
      }
    }
    localStorage.setItem('left', JSON.stringify(rows));
    this.totalDuration = prevDuration + maxDurationForScene;

    // add background white canvas
    const _row = {
      Scene: 0,
      Subscene: 0,
      Type: "Background",
      Left: 0,
      Top: 0,
      Width: 1,
      Height: 1,
    };

    rows.splice(0, 0, _row);

    this.rows = rows;
  }

  getSceneMeta(row) {
    let direction = "";
    let type = "";

    if (row.Type == "Text") {
      direction = "v";
      type = "text";
    } else if (row.Type == "Image") {
      if ((row.Width == 1 || row.Width == 0.5) && row.Height < 1.0)
        direction = "v";
      if (row.Height == 1 && row.Width < 1.0) direction = "h";
      type = "image";
    }

    return { direction, type };
  }

  sleep(num) {
    let now = new Date();
    let stop = now.getTime() + num;
    while(true) {
        now = new Date();
        if(now.getTime() > stop) return;
    }
  }

  parseScene(duration, caption = false,canvasvolume) {
    this.rows.forEach((row, index) => {
      if (row.Type == "Background") {
        const { canvas } = this.createBlankCanvas(true);

        const node = this.vCtx.canvas(canvas);
        node.start(0);
        node.stop(this.totalDuration);

        const combineEffect = this.vCtx.compositor(
          this.getCombineDescription()
        );
        node.connect(combineEffect);
        combineEffect.connect(this.vCtx.destination);
      }
      if (row.Type == "Music") {
        const node = this.vCtx.audio(row.Filename);
        node.start(row.Start);
        node.stop(Math.min(row.End, duration));
        this.fadeinout=row.Props;

        const combineEffect = this.vCtx.compositor(
          this.getCombineDescription()
        );
        node.connect(combineEffect);
        combineEffect.connect(this.vCtx.destination);
      }
      if (row.Type == "Video") {
        const node = this.vCtx.video(row.Filename);
        if (this.isMusicAvailable) node.volume = 0;
        this.vCtx.volume = canvasvolume/100;
        if(this.vCtx.state){
          const interval = setInterval(() => {
            node.volume = 0;
          }, 0);
        }
        node.start(row.Start);
        node.stop(row.End);

        const combineEffect = this.vCtx.compositor(
          this.getCombineDescription(row.Left, row.Top, row.Width, row.Height)
        );
        node.connect(combineEffect);
        combineEffect.connect(this.vCtx.destination);
      }
      if (row.Type == "Image") {
        let canvas0;

        if (row.Animation && row.Animation != "none") {
          const { canvas } = this.createBlankCanvas(true);

          canvas0 = canvas;
        }

        const node = this.vCtx.image(row.Filename);

        node.start(row.Start);
        node.stop(row.End);

        row.Left = this.calculateHorizontalPositionOfImage(row, node);
        row.Top = this.calculateVerticalPositionOfImage(row, node);
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
      }
      if (row.Type == "Text") {
        let x = 0, y = 0;
        if (this.currentScene !== row.Scene ) {
          this.currentScene = row.Scene;
          this.drawOnceBG = 0;
        }
        let canvas0;

        if (row.Animation && row.Animation != "none") {
          const { canvas } = this.createBlankCanvas();
          canvas0 = canvas;
        }

        const { canvas, ctx } = this.createBlankCanvas();
        const txt = this.isStringStar(row.Text)
          ? row.Text.substring(0, 5)
          : row.Text;

        if (!this.drawOnceBG) {
          ctx.fillStyle = row.Background_Color;

          if(row.Left >= canvas.width / 2) {
            x = canvas.width / 2
          }
          ctx.fillRect(x,y, canvas.width * row.Width, canvas.height);
          this.drawOnceBG = 1
        }
        ctx.font = `${row.Size * this.RS}px ${row.Font_Name}`;
        let kerning = (row['Kerning'] * parseFloat(this.RS.toFixed(2))).toFixed(2);
        ctx.letterSpacing = kerning + 'px';
        ctx.fillStyle = row.Color;
        ctx.textBaseline = "middle";
        ctx.fillText(txt, row.Left, row.Top);

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

      }
      if (row.Type == "LH" || row.Type == "RH" || row.Type == "FF") {

          let canvas0;
          var rgbStart = row.Background_Color;
          var rgbEnd = row.Color;

          if (row.Animation && row.Animation != "none") {
            const { canvas,ctx } = this.createBlankCanvas(true, row.Background_Color);

            if(row.Animation == "bgr-c2c"){
              if(this.currentTime <= 0){
                ctx.globalAlpha=1;
              }else{
                ctx.globalAlpha = 0.8;
              }
              ctx.fillStyle=rgbStart;
              ctx.fillRect(0,0,canvas.width,canvas.height);

              setTimeout(function(){
                ctx.fillStyle=rgbEnd;
                ctx.globalAlpha = 1,
                ctx.fillRect(0,0,canvas.width,canvas.height)
              },((row.Start + 1) + row.Duration/2) * 1000);
            }
            canvas0 = canvas;
          }

          const { canvas } = document.getElementById("preview");

          let c_left = 0.0;
          let c_top = 0.0;
          let c_width = 1.0;
          let c_height = 1.0;

          if(row.Type == "LH") {
            c_left = 0.0;
            c_width = 0.5;
          } else if(row.Type == "RH") {
            c_left = 0.5;
            c_width = 0.5;
          } else {
            c_left = 0.0;
            c_width = 1.0;
          }

          const node = this.vCtx.canvas(canvas);
          node.start(row.Start);
          node.stop(row.End);

          let combineEffect;
          combineEffect = this.vCtx.compositor(
            this.getCombineDescription(c_left, c_top, c_width, c_height)
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
              transition.connect(combineEffect);
            } else if (row.Animation == "fadeout") {
              transition = this.vCtx.transition(
                VideoContext.DEFINITIONS.CROSSFADE
              );
              transition.transition(row.Start, row.End, 0.0, 1.0, "mix");
              node.connect(transition);
              fakeNode.connect(transition);
              transition.connect(combineEffect);
            } else if (row.Animation == "bgr-c2c") {

              for (let index = 0; index < 2; index++) {
                transition = this.vCtx.transition(
                  VideoContext.DEFINITIONS.CROSSFADE
                );

                if(index == 0){
                  if(this.currentTime <= 0){
                    transition.transition(row.Start, ((row.Start + 0.8) + row.Duration/2), 0.0, 1.0, "mix");
                  } else{
                    transition.transition(row.Start - 0.5, ((row.Start + 0.8) + row.Duration/2), 0.0, 1.0, "mix");
                  }
                  fakeNode.connect(transition);
                  node.connect(transition);
                } else {
                  transition.transition(((row.Start - 0.8) + row.Duration/2), (row.End + 0.5), 0.0, 1.0, "mix");
                  node.connect(transition);
                  fakeNode.connect(transition);
                }
                transition.connect(combineEffect);
              }
            }
          } else {
            node.connect(combineEffect);
          }
          combineEffect.connect(this.vCtx.destination);
      }
    });

    this.rows.forEach((row) => {
      if (row.Type == "VTT" && caption == true) {
        const { canvas, ctx } = this.createBlankCanvas();

        ctx.font = `${row.Size * this.RS}px ${row.Font_Name}`;
        ctx.fillStyle = row.Color;
        ctx.textBaseline = "middle";
        const vttWidth = ctx.measureText(row.Text).width;
        const vttLeft = canvas.width / 2 - vttWidth / 2;
        ctx.fillText(row.Text, vttLeft, canvas.height - 30);
        const node = this.vCtx.canvas(canvas);
        node.start(row.Start);
        node.stop(row.End);

        const combineEffect = this.vCtx.compositor(
          this.getCombineDescription()
        );
        node.connect(combineEffect);
        combineEffect.connect(this.vCtx.destination);
      }
    });
  }

  createBlankCanvas(isFill = false, color = 'white') {
    const canvas = document.createElement("canvas");
    canvas.width = this.canvas.width;
    canvas.height = this.canvas.height;
    const ctx = canvas.getContext("2d");

    if (isFill) {
      ctx.fillStyle = color;
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
    const assetRows = this.rows.filter(
      (r) => r.Type == "Image" || r.Type == "Video"
    );
    const assetPromises = assetRows.map((r) => {
      if (r.Type == "Image") {
        return this.loadImageAsset(r.Filename);
      } else if (r.Type == "Video") {
        return this.loadVideoAsset(r.Filename);
      }
    });

    return Promise.all([this.loadAllFonts(), ...assetPromises]);
  }

  isValidJSONString(str) {
    try {
    } catch (e) {
        return false;
    }
    return true;
  }

  play(time = 0) {
    this.currentTime = time;
    const [track] = this.canvas.captureStream().getVideoTracks();
    this.canvas.onended = (evt) => track.stop();
    console.log('track', track);
    this.vCtx.play();
    // fadein-out
    try {
    let  person = JSON.parse(this.fadeinout);


      if (isNaN(this.vCtx.volume)) {
        volume= parseFloat(0.1);
      }

      let timesRun = 0;
      const interval = setInterval(() => {
        timesRun += 1;
        this.vCtx.volume =parseFloat('.' + (timesRun * 2));
        if(timesRun === person.fadein){
          clearInterval(interval);
        }
      }, 1000);

      let timeDecrese=this.totalDuration;
      const interval2 = setInterval(() => {
        timeDecrese -= 1;
        if(timeDecrese===0){
          clearInterval(interval2);
        }
        if(timeDecrese <= person.fadeout){
          this.vCtx.volume =parseFloat('.' + timeDecrese);
        }
        else
        {
          if(timeDecrese === this.totalDuration){
            clearInterval(interval2);
          }
        }
      }, 1000);

  } catch (error) {
      console.log("Invalid JSON string");
  }

    // if(this.isValidJSONString(this.fadeinout)){
    //   //cool we are valid, lets parse
    //   var person= JSON.parse(this.fadeinout);

    //   if (isNaN(this.vCtx.volume)) {
    //     volume= parseFloat(0.1);
    //   }

    //   let timesRun = 0;
    //   const interval = setInterval(() => {
    //     timesRun += 1;
    //     this.vCtx.volume =parseFloat('.' + (timesRun * 2));
    //     if(timesRun === person.fadein){
    //       clearInterval(interval);
    //     }
    //   }, 1000);

    //   let timeDecrese=this.totalDuration;
    //   const interval2 = setInterval(() => {
    //     timeDecrese -= 1;
    //     if(timeDecrese===0){
    //       clearInterval(interval2);
    //     }
    //     if(timeDecrese <= person.fadeout){
    //       this.vCtx.volume =parseFloat('.' + timeDecrese);
    //     }
    //     else
    //     {
    //       if(timeDecrese === this.totalDuration){
    //         clearInterval(interval2);
    //       }
    //     }
    //   }, 1000);
    // }
    // over
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
      else return "PLAYING";
    } else {
      return "NO";
    }
  }

  setRS(rs) {
    this.RS = rs;
  }
  getTextRect(row) {
    let rs = 1.0 / (1080 / this.canvas.height);
    const fontSize = row.Size * rs;
    let kerning = (row.Kerning * parseFloat(this.RS.toFixed(2))).toFixed(2);
    const t = document.getElementById("test-text");
    t.style.fontSize = `${fontSize}px`;
    t.style.fontFamily = row.Font_Name;

    switch (getValueMeasurementLineSpacing(row.Line_Spacing)) {
      case 'px':
        t.style.lineHeight = 1;
        t.style.paddingTop = (parseFloat(row.Line_Spacing) / 2) * this.RS + 'px';
        t.style.paddingBottom = (parseFloat(row.Line_Spacing) / 2) * this.RS + 'px';
        break;
      case 'pt':
        let halfValueOfLineSpacingInPx = ((parseFloat(row.Line_Spacing) / 72 * 96) * this.RS) / 2;
        t.style.lineHeight = 1;
        t.style.paddingTop = halfValueOfLineSpacingInPx + 'px';
        t.style.paddingBottom = halfValueOfLineSpacingInPx + 'px';
        break;
      default:
        t.style.lineHeight = row.Line_Spacing;
        t.style.paddingTop = '0px';
        t.style.paddingBottom = '0px';
        break;
    }
    t.style.whiteSpace = 'nowrap';
    t.style.display = 'inline';
    t.style.letterSpacing = `${kerning}px`;

    t.innerHTML = this.isStringStar(row.Text) ? "★★★★★" : row.Text;

    let boundingClientRect = t.getBoundingClientRect();
    return {
      width: boundingClientRect.width,
      height: boundingClientRect.height
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

  calculateVerticalPositionOfText(row, top, textRows, ) {
    if (row.Top && row.Top > 0) {
      return (row.Top + (row.textHeight / this.canvas.height / 2 ))  * ( this.canvas.height * row.Height)
    }

    let filteredTextRows = textRows.filter(textRow => {
      return !row.Top && textRow.Type === "Text"
    });
    let indexOfFilteredTextRow = filteredTextRows.findIndex( textRow => {
      return row.originalIndex === textRow.originalIndex
    });

    let sumRowsHeight = 0;

    switch (row.AlignV) {
      case "Top":
        if (indexOfFilteredTextRow === 0) return textRows[indexOfFilteredTextRow].textHeight / 2;
        return filteredTextRows[indexOfFilteredTextRow - 1].Top
            + filteredTextRows[indexOfFilteredTextRow - 1].textHeight / 2
            + (textRows[indexOfFilteredTextRow].textHeight / 2);

      case "Center":
        if (indexOfFilteredTextRow !== 0 && filteredTextRows[indexOfFilteredTextRow - 1].Top) {
          let currentTop = filteredTextRows[indexOfFilteredTextRow - 1].Top
              + filteredTextRows[indexOfFilteredTextRow - 1].textHeight / 2
              + filteredTextRows[indexOfFilteredTextRow].textHeight / 2;
          return filteredTextRows.length > 1 ?
              currentTop
              : this.canvas.height / 2.0;
        }

        sumRowsHeight = filteredTextRows.filter(row => row.AlignV === 'Center').reduce(
            (accumulator, currentValue) => {
              return accumulator + currentValue.textHeight;
            },
            0
        );
        let coef = 2;
          if(filteredTextRows.length > 4) {
            coef = 2.5
          } else {
            coef = filteredTextRows.length % 2 === 0 ? 2.25 : 2.1;
          }
        return filteredTextRows.length > 1 ?
            (this.canvas.height - sumRowsHeight / 2) / coef
            : this.canvas.height / 2.0;
      case "Bottom":
        if (indexOfFilteredTextRow && filteredTextRows[indexOfFilteredTextRow - 1].Top) {
          return filteredTextRows[indexOfFilteredTextRow - 1].Top
              + filteredTextRows[indexOfFilteredTextRow - 1].textHeight / 2
              + filteredTextRows[indexOfFilteredTextRow].textHeight / 2;
        }
        sumRowsHeight = filteredTextRows.filter(row => row.AlignV === 'Bottom').reduce(
            (accumulator, currentValue, index, array) => {
              return accumulator + currentValue.textHeight;
            },
            0
        );

        return this.canvas.height * row.Height - (sumRowsHeight - row.textHeight / 2);

      default:
        if (indexOfFilteredTextRow !== 0 && filteredTextRows[indexOfFilteredTextRow - 1].Top) {
          let currentTop = filteredTextRows[indexOfFilteredTextRow - 1].Top
              + filteredTextRows[indexOfFilteredTextRow - 1].textHeight / 2
              + filteredTextRows[indexOfFilteredTextRow].textHeight / 2;
          return filteredTextRows.length > 1 ?
              currentTop
              : this.canvas.height / 2.0;
        }

        sumRowsHeight = filteredTextRows.filter(row => row.AlignV === 'Center').reduce(
            (accumulator, currentValue, index, array) => {
              if (row.Width === 1) {
                return accumulator + currentValue.textHeight;
              }
              if (index === 0) {
                return accumulator + currentValue.textHeight / 2;
              }
              return accumulator + currentValue.textHeight / 2 + accumulator + array[index - 1].textHeight / 2
            },
            0
        );

        if (row.Width === 1) {
          return filteredTextRows.length > 1 ?
              (this.canvas.height - sumRowsHeight / 1.3) / 2.0
              : this.canvas.height / 2.0;
        }

        return filteredTextRows.length > 1 ?
            (this.canvas.height - sumRowsHeight / 2) / 2.0
            : this.canvas.height / 2.0;
    }
  }

  calculateHorizontalPositionOfText(row) {

    let currentRowWidth = this.canvas.width * row.Width;
    let textWidth = this.getTextRect(row).width;
    if (row.Left && row.Left > 1) {
      return row.Left / this.canvas.width;
    }
    else if(row.Left && row.Left > 0 && row.Left <= 1) {
      return row.Left * this.canvas.width;
    }

    switch (row.AlignH) {
      case "Left":
        return 0;
      case "Center":
        return (currentRowWidth - textWidth) / 2;
      case "Right":
        return (currentRowWidth - textWidth);
      case this.alignH.leftFF:
      case this.alignH.leftLH:
        return 0;
      case this.alignH.leftRH:
        return this.canvas.width * 0.5;
      case this.alignH.rightFF:
        return this.canvas.width - textWidth;
      case this.alignH.rightLH:
        return this.canvas.width - textWidth;
      case this.alignH.rightRH:
        return this.canvas.width * 0.5 - textWidth;
      case this.alignH.centerFF:
        return this.canvas.width * 0.5 - textWidth / 2;
      case this.alignH.centerLH:
        return this.canvas.width / 4 - textWidth / 2;
      case this.alignH.centerRH:
        return this.canvas.width / 4 * 3 - textWidth / 2;
      case this.alignH.fF:
        return row.Left;
      case this.alignH.lH:
        return row.Left / 2;
      case this.alignH.rH:
        return this.canvas.width * 0.5 + row.Left / 2;
      default:
        return currentRowWidth * 0.5 - textWidth / 2;
    }
  }

  calculateHorizontalPositionOfImage(imageRow) {
    const {AlignH: imageAlignH} = imageRow;

    switch (imageAlignH) {
      case this.alignH.leftFF:
      case this.alignH.leftLH:
        return 0;
      case this.alignH.leftRH:
        return 0.5;
      case this.alignH.rightFF:
        return 1 - imageRow.Width;
      case this.alignH.rightLH:
        return 0.5 - imageRow.Width;
      case this.alignH.rightRH:
        return 1 - imageRow.Width;
      case this.alignH.centerFF:
        return 0.5 - imageRow.Width / 2;
      case this.alignH.centerLH:
        return 1/4 - imageRow.Width/ 2;
      case this.alignH.centerRH:
        return 1/4 * 3 - imageRow.Width / 2;
      case this.alignH.fF:
        return imageRow.Left;
      case this.alignH.lH:
        return imageRow.Left / 2;
      case this.alignH.rH:
        return 0.5 + imageRow.Left / 2;
      default:
        return imageRow.Left;
    }
  }

  calculateVerticalPositionOfImage(imageRow) {
    const {Top: imageTop, AlignV: imageAlignV, Height: imageHeight} = imageRow;

    if (imageTop) {
      return imageTop;
    }
    switch (imageAlignV) {
      case this.alignV.top:
        return 0;
      case this.alignV.center:
        return 0.5 - imageHeight / 2;
      case this.alignV.bottom:
        return 1 - imageHeight;
      default: return imageTop;
    }
  }
}



export default Preview;
