# I grepped the original timeeffect CSV history with these commands:

PROJECT=timeeffect
GITUSER=rubo77
rsync -av rsync://a.cvs.sourceforge.net/cvsroot/$PROJECT/\* cvs
svn export --username=guest http://cvs2svn.tigris.org/svn/cvs2svn/trunk cvs2svn-trunk
cp ./cvs2svn-trunk/cvs2git-example.options ./cvs2git.options
vim cvs2git.options # edit run_options.set_project
cvs2svn-trunk/cvs2git --options=cvs2git.options --fallback-encoding utf-8
#create at https://github.com/$GITUSER/$PROJECT.git
git clone git@github.com:$GITUSER/$PROJECT.git $PROJECT-github
cd $PROJECT-github
cat ../cvs2git-tmp/git-{blob,dump}.dat | git fast-import
git log
git reset --hard
git push
