const NAME = 'camera'
const NAMESPACE = `admin4b.${NAME}`

const DataAttribute = {
  DATA_SIZE: 'data-size'
}

const Event = {
  PLAY: 'play',
  SNAPSHOT: 'snapshot',
  STOP: 'stop',
  TRIGGER_ERROR: `${NAME}:error`,
  TRIGGER_NOT_SUPPORTED: `${NAME}:notSupported`,
  TRIGGER_PLAY: `${NAME}:play`,
  TRIGGER_STOP: `${NAME}:stop`,
  TRIGGER_SNAPSHOT: `${NAME}:snapshot`
}

const Prop = {
  PLAYING: `${NAMESPACE}.playing`,
  STREAM: `${NAMESPACE}.stream`
}

export {
  NAME,
  DataAttribute,
  Event,
  Prop
}
