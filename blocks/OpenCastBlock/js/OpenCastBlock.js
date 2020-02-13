import StudentView from './student_view.js'
import AuthorView from './author_view.js'

export default courseware.block_types.add({
  name: 'OpenCastBlock',

  content_block: true,

  views: {
    student: StudentView,
    author: AuthorView
  }
});
