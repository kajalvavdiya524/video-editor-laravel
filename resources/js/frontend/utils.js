export function delay(time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}

export const deepCopy = (a) => {
  if (!a) return a;
  return JSON.parse(JSON.stringify(a));
};
