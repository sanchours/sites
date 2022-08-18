import React from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import styles from '../index.less'

let dragingIndex = -1;
class BodyRow extends React.Component {
  render() {
    const { isOver, connectDragSource, connectDropTarget, moveRow, ...restProps } = this.props;

    let { className } = restProps;
    if (isOver) {
      if (restProps.index > dragingIndex) {
        className += ' drop-over-downward';
      }
      if (restProps.index < dragingIndex) {
        className += ' drop-over-upward';
      }
    }

    return connectDragSource(connectDropTarget(<tr {...restProps} className={className} />));
  }
}

const rowSource = {
  beginDrag(props) {
    const {index, setEditingKey} = props;
    const dragRowKey = props['data-row-key'];

    dragingIndex = index;

    if ( setEditingKey ){
      // выходим из режима редактирования
      setEditingKey('');
    }

    return {
      index,
      dragRowKey,
    };
  },
  canDrag(props) {
    const {isDraggable} = props;
    const dragRowKey = props['data-row-key'];

    return isDraggable() && !(dragRowKey === undefined || isNaN(Number(dragRowKey)));
  },
};

const rowTarget = {
  drop(props, monitor) {
    const dragIndex = monitor.getItem().index;
    const dragRowKey = monitor.getItem().dragRowKey;
    const hoverIndex = props.index;
    const hoverRowKey = props['data-row-key'];

    // Don't replace items with themselves
    //
    if (dragIndex === hoverIndex || dragRowKey === hoverRowKey) {
      return;
    }

    // Time to actually perform the action
    props.moveRow(dragIndex, hoverIndex, dragRowKey, hoverRowKey);

    // Note: we're mutating the monitor item here!
    // Generally it's better to avoid mutations,
    // but it's good here for the sake of performance
    // to avoid expensive index searches.
    monitor.getItem().index = hoverIndex;
  },
};

const DragableBodyRow = DropTarget('row', rowTarget, (connect, monitor) => ({
  connectDropTarget: connect.dropTarget(),
  isOver: monitor.isOver(),
}))(
  DragSource('row', rowSource, connect => ({
    connectDragSource: connect.dragSource(),
  }))(BodyRow)
);

export default DragableBodyRow;
