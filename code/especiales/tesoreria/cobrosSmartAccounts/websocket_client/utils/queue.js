class Queue {
  constructor() {
    this.elements = [];
  }

  add(element) {
    if (!(element in this.elements)) {
      this.elements.push(element);
    }
  }

  get() {
    return this.elements;
  }

  remove(element) {
    if (this.elements.includes(element)) {
      const index = this.elements.indexOf(element);
      this.elements.splice(index, 1);
    }
  }
}

export default Queue;
