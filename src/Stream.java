/*
	This file is part of "stream.m" software, a video broadcasting tool
	compatible with Google's WebM format.
	Copyright (C) 2011 Varga Bence

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

import java.net.*;
import java.io.*;
import java.util.*;
import threadedevent.GeneralEventProducer;

class Stream extends GeneralEventProducer {
    
    protected static final long NUMBER_OF_FRAGMENT_PER_FILE = 3;
    protected static final String workingDir = "/tmp/zewall/"; // Dont forget trailing slash
    protected static final String convertBin = "/usr/local/bin/convert.sh";
    
    protected int fragmentNumber = 10;
    protected int fileNumber = 0;
    protected String streamID = "out";
    protected String streamName = "out";
    
	private MovieFragment fragment;
	private int fragmentAge;
	
	private byte[] header;
	
	private boolean runs = true;
	
	public synchronized boolean running() {
		return runs;
	}
	
	public synchronized void stop() {
		runs = false;
        // ARNAUD
        // Remove all file in workingDir + streamID
        try{
            File dir = new File(workingDir + streamID);
            if (dir.exists()){
                String[] children = dir.list();
                // If files in this dir, first delete files
                for (int i=0; i<children.length; i++) {
                    if ( ! (new File(dir, children[i])).delete()) throw new Exception("An error occured while trying to remove file: " + workingDir + streamID + children[i]);
                }
                if ( ! (new File(workingDir + streamID)).delete()) throw new Exception("An error occured while trying to remove working directory: " + workingDir + streamID);
            }
        }
        catch (Exception e){//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
	}
	
	public synchronized int getFragmentAge() {
		return fragmentAge;
	}
	
	public synchronized MovieFragment getFragment() {
		return fragment;
	}
	
	public synchronized byte[] getHeader() {
		return header;
	}
	
	public synchronized void setHeader(byte[] newHeader) {
		header = newHeader;
	}
	
	public synchronized void pushFragment(MovieFragment newFragment) {
		if (fragmentAge == 0)
			postEvent(new ServerEvent(this, this, ServerEvent.INPUT_FIRST_FRAGMENT));
		fragment = newFragment;
		fragmentAge++;
        
        // ARNAUD: Ecriture du fragment dans un fichier
        try{
            // Check if we need to write to another file
            if (fragmentNumber >= NUMBER_OF_FRAGMENT_PER_FILE){
                if (fileNumber != 0){
                    // Yes, launch ffmpeg to transcode the created file
                    // build the system command we want to run
                    List<String> commands = new ArrayList<String>();
                    commands.add("/bin/sh");
                    commands.add("-c");
                    commands.add(convertBin + " --outputfolder " + streamName + " --inputfile " + workingDir + streamID + "/" + fileNumber + ".webm --outputfilename " + streamName + fileNumber);
                    //commands.add(convertBin + " " + workingDir + streamID + "/" + fileNumber + ".webm" + " " + streamName + " " + streamName + fileNumber);
                    System.out.println(convertBin + " --outputfolder " + streamName + " --inputfile " + workingDir + streamID + "/" + fileNumber + ".webm --outputfilename " + streamName + fileNumber);

                    // execute the command
                    SystemCommandExecutorThread commandExecutor = new SystemCommandExecutorThread(commands);
                    //int result = commandExecutor.executeCommand();
                    commandExecutor.start();
                    System.out.println("FFMpeg started for " + workingDir + streamName + "/" + fileNumber);
                    /*
                    // get the stdout and stderr from the command that was run
                    StringBuilder stdout = commandExecutor.getStandardOutputFromCommand();
                    StringBuilder stderr = commandExecutor.getStandardErrorFromCommand();
                    
                    // print the stdout and stderr
                    System.out.println("The numeric result of the command was: " + result);
                    System.out.println("STDOUT:");
                    System.out.println(stdout);
                    System.out.println("STDERR:");
                    System.out.println(stderr);
                    */
                }
                fileNumber++;
            }
            
            // Check if workingDir exists
            if ( ! (new File(workingDir + streamID)).exists()){
                if ( ! (new File(workingDir + streamID)).mkdirs()) throw new Exception("An error occured while trying to create working directory: " + workingDir + streamID);
            }
            
            // Create file 
            FileOutputStream fstream = new FileOutputStream(workingDir + streamID + "/" + fileNumber + ".webm", true);
            // If new file, write header
            if (fragmentNumber >= NUMBER_OF_FRAGMENT_PER_FILE){
                fstream.write(header);
                fragmentNumber = 0;
            }
            fstream.write(fragment.getData(), 0, fragment.length());
            //Close the output stream
            fstream.close();
            System.out.println("Writing file: " + workingDir + streamID + "/" + fileNumber + ".webm");
            fragmentNumber++;
        } catch (Exception e){//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
        // FIN ARNAUD
        
        
    }
}
